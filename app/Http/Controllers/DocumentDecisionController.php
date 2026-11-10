<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentDecisionRequest;
use App\Models\AuditLog;
use App\Models\DocumentDecision;
use App\Models\DocumentObservation;
use App\Models\DocumentTransmission;
use App\Models\DocumentVersion;
use App\Models\TransmissionDestinataire;
use App\Models\User;
use App\Notifications\CorrectionDemandee;
use App\Notifications\DocumentValide;
use App\Services\CircuitVisaService;
use App\Services\DocumentStampingService;
use Illuminate\Support\Facades\Auth;

class DocumentDecisionController extends Controller
{
    // Écran de consolidation : observations des collaborateurs de la structure du responsable
    public function create(DocumentVersion $documentVersion)
    {
        abort_if($documentVersion->dossier->isArchived(), 403, 'Ce document est archivé et ne peut plus être modifié.');
        abort_unless(
            app(CircuitVisaService::class)->peutDecider(Auth::user(), $documentVersion),
            403,
            'Votre structure n\'a pas (ou plus) de visa à donner sur cette version.'
        );

        $documentVersion->load([
            'dossier.documentType',
            'affectations.controleur',
            'affectations.observations.auteur',
        ]);
        $affectations = $this->affectationsDeMaStructure($documentVersion);

        return view('dossiers.decision', compact('documentVersion', 'affectations'));
    }

    /** Affectations des collaborateurs de la même structure que le responsable connecté. */
    private function affectationsDeMaStructure(DocumentVersion $version)
    {
        $type = Auth::user()->structureVisa();

        return $version->affectations->filter(fn ($a) => $a->controleur?->structureVisa() === $type)->values();
    }

    public function store(StoreDocumentDecisionRequest $request, DocumentVersion $documentVersion)
    {
        abort_if($documentVersion->dossier->isArchived(), 403, 'Ce document est archivé et ne peut plus être modifié.');
        $circuit = app(CircuitVisaService::class);
        abort_unless(
            $circuit->peutDecider(Auth::user(), $documentVersion),
            403,
            'Votre structure n\'a pas (ou plus) de visa à donner sur cette version.'
        );

        $observationsRetenues = $request->observations_retenues ?? [];
        $documentVersion->load('affectations.controleur');
        $affectationsIds = $this->affectationsDeMaStructure($documentVersion)->pluck('id');

        // Seules les observations de sa propre structure sont concernées.
        DocumentObservation::whereIn('document_assignment_id', $affectationsIds)->update(['retenue' => false]);

        if (!empty($observationsRetenues)) {
            DocumentObservation::whereIn('id', $observationsRetenues)
                ->whereIn('document_assignment_id', $affectationsIds)
                ->update(['retenue' => true]);
        }

        DocumentDecision::create([
            'document_version_id' => $documentVersion->id,
            'decision' => $request->decision,
            'validateur_id' => Auth::id(),
            'date_decision' => now(),
            'commentaires' => $request->commentaires,
        ]);

        if ($request->decision === 'valide') {
            // Visa de validation (cachet + signature du validateur) : enregistré avant le tampon final
            // pour figurer sur le document validé.
            app(\App\Services\VisaService::class)->enregistrer($request, Auth::user(), $documentVersion, 'validation');

            $circuit->appliquerDecision($documentVersion, Auth::user(), 'valide', $request->commentaires);
            $complets = $circuit->visasComplets($documentVersion);

            // Le document est validé dès le premier visa ; il est re-tamponné et re-scellé à chaque visa
            // supplémentaire, pour que les cachets de toutes les structures y figurent.
            app(DocumentStampingService::class)->tamponner($documentVersion);

            AuditLog::enregistrer($complets ? 'VALIDATION' : 'VISA', $documentVersion->dossier_id, [
                'document_version_id' => $documentVersion->id,
                'structure' => Auth::user()->structureVisa(),
            ]);

            $documentVersion->dossier->creePar->notify(new DocumentValide($documentVersion->dossier));

            if ($complets) {
                // Le Maître d'Ouvrage est informé quand tous les visas sont obtenus (simple notification,
                // aucune transmission automatique du fichier — ça reste un choix manuel du BC).
                $utilisateursMO = User::whereHas('structure', fn ($q) => $q->where('type', 'maitre_ouvrage'))
                    ->where('actif', true)
                    ->get();
                foreach ($utilisateursMO as $utilisateurMO) {
                    $utilisateurMO->notify(new DocumentValide($documentVersion->dossier));
                }
            }

            return redirect()->route('dossiers.show', $documentVersion->dossier_id)
                ->with('success', $complets ? 'Document validé : tous les visas sont obtenus.' : 'Document validé. Votre visa est enregistré ; les autres structures peuvent encore viser.');
        }

        $circuit->appliquerDecision($documentVersion, Auth::user(), 'a_corriger', $request->commentaires);

        // Les observations retenues sont transmises automatiquement à l'émetteur du dossier
        $transmission = DocumentTransmission::create([
            'dossier_id' => $documentVersion->dossier_id,
            'document_version_id' => $documentVersion->id,
            'emetteur_id' => Auth::id(),
            'mode' => 'diffusion_validation',
            'commentaire' => $request->commentaires,
            'date_transmission' => now(),
        ]);

        TransmissionDestinataire::create([
            'document_transmission_id' => $transmission->id,
            'user_id' => $documentVersion->dossier->cree_par,
        ]);

        AuditLog::enregistrer('CORRECTION_DEMANDEE', $documentVersion->dossier_id, [
            'document_version_id' => $documentVersion->id,
        ]);

        $documentVersion->dossier->creePar->notify(new CorrectionDemandee($documentVersion->dossier, $request->commentaires));

        return redirect()->route('dossiers.show', $documentVersion->dossier_id)
            ->with('success', 'Correction demandée. L\'émetteur a été notifié.');
    }
}
