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
use App\Services\DocumentStampingService;
use Illuminate\Support\Facades\Auth;

class DocumentDecisionController extends Controller
{
    // Écran de consolidation : toutes les observations des contrôleurs affectés
    public function create(DocumentVersion $documentVersion)
    {
        abort_unless($documentVersion->statut === 'en_analyse', 403, 'Cette version n\'est pas (ou plus) en analyse.');

        $documentVersion->load([
            'dossier.documentType',
            'affectations.controleur',
            'affectations.observations.auteur',
        ]);

        return view('dossiers.decision', compact('documentVersion'));
    }

    public function store(StoreDocumentDecisionRequest $request, DocumentVersion $documentVersion)
    {
        abort_unless($documentVersion->statut === 'en_analyse', 403, 'Cette version n\'est pas (ou plus) en analyse.');

        $observationsRetenues = $request->observations_retenues ?? [];

        DocumentObservation::whereIn(
            'document_assignment_id',
            $documentVersion->affectations()->pluck('id')
        )->update(['retenue' => false]);

        if (!empty($observationsRetenues)) {
            DocumentObservation::whereIn('id', $observationsRetenues)->update(['retenue' => true]);
        }

        DocumentDecision::create([
            'document_version_id' => $documentVersion->id,
            'decision' => $request->decision,
            'validateur_id' => Auth::id(),
            'date_decision' => now(),
            'commentaires' => $request->commentaires,
        ]);

        if ($request->decision === 'valide') {
            $documentVersion->update(['statut' => 'valide']);
            $documentVersion->dossier->update(['statut' => 'valide']);

            app(DocumentStampingService::class)->tamponner($documentVersion);

            AuditLog::enregistrer('VALIDATION', $documentVersion->dossier_id, [
                'document_version_id' => $documentVersion->id,
            ]);

            $documentVersion->dossier->creePar->notify(new DocumentValide($documentVersion->dossier));

            // Le Maître d'Ouvrage est informé qu'un document est validé (simple notification,
            // aucune transmission automatique du fichier — ça reste un choix manuel du BC).
            $utilisateursMO = User::whereHas('structure', fn ($q) => $q->where('type', 'maitre_ouvrage'))
                ->where('actif', true)
                ->get();
            foreach ($utilisateursMO as $utilisateurMO) {
                $utilisateurMO->notify(new DocumentValide($documentVersion->dossier));
            }

            return redirect()->route('dossiers.show', $documentVersion->dossier_id)
                ->with('success', 'Document validé.');
        }

        $documentVersion->update(['statut' => 'a_corriger']);
        $documentVersion->dossier->update(['statut' => 'a_corriger']);

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
