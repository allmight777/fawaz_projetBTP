<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentTransmissionRequest;
use App\Models\AuditLog;
use App\Models\Dossier;
use App\Models\DocumentTransmission;
use App\Models\Structure;
use App\Models\TransmissionDestinataire;
use App\Models\User;
use App\Notifications\DocumentArchive;
use App\Notifications\DocumentTransmis;
use Illuminate\Support\Facades\Auth;

class DocumentTransmissionController extends Controller
{
    // Workflow 1 : transmission simple, choix des destinataires par l'émetteur
    public function transmettreForm(Dossier $dossier)
    {
        abort_unless($dossier->cree_par === Auth::id(), 403, 'Seul l\'émetteur du dossier peut choisir ses destinataires.');

        if ($dossier->mode_traitement !== 'simple') {
            abort(403, 'Ce document nécessite une validation, il ne peut pas être transmis directement.');
        }
        abort_if($dossier->statut === 'archive', 403, 'Ce document est archivé et ne peut plus être modifié.');

        $structures = Structure::actives()->get();
        $users = User::where('actif', true)->get();

        return view('dossiers.transmettre', compact('dossier', 'structures', 'users'));
    }

    public function transmettreSimple(StoreDocumentTransmissionRequest $request, Dossier $dossier)
    {
        abort_unless($dossier->cree_par === Auth::id(), 403, 'Seul l\'émetteur du dossier peut choisir ses destinataires.');

        if ($dossier->mode_traitement !== 'simple') {
            abort(403, 'Ce document nécessite une validation, il ne peut pas être transmis directement.');
        }
        abort_if($dossier->statut === 'archive', 403, 'Ce document est archivé et ne peut plus être modifié.');

        $this->creerTransmission($dossier, 'simple', $request);

        $dossier->update(['statut' => 'transmis']);

        AuditLog::enregistrer('TRANSMISSION', $dossier->id);

        return redirect()->route('dossiers.show', $dossier)->with('success', 'Document transmis.');
    }

    // Diffusion manuelle après validation par le Bureau de Contrôle (aucune transmission automatique)
    public function diffusionForm(Dossier $dossier)
    {
        if ($dossier->statut !== 'valide') {
            abort(403, 'Ce document n\'est pas encore validé.');
        }

        $structures = Structure::actives()->get();
        $users = User::where('actif', true)->get();

        return view('dossiers.diffusion', compact('dossier', 'structures', 'users'));
    }

    public function diffuser(StoreDocumentTransmissionRequest $request, Dossier $dossier)
    {
        if ($dossier->statut !== 'valide') {
            abort(403, 'Ce document n\'est pas encore validé.');
        }

        $this->creerTransmission($dossier, 'diffusion_validation', $request);

        AuditLog::enregistrer('DIFFUSION', $dossier->id);

        if ($request->boolean('archiver')) {
            $dossier->update(['statut' => 'archive']);
            AuditLog::enregistrer('ARCHIVAGE', $dossier->id);
            $dossier->creePar->notify(new DocumentArchive($dossier));

            return redirect()->route('dossiers.show', $dossier)->with('success', 'Document diffusé et archivé.');
        }

        return redirect()->route('dossiers.show', $dossier)->with('success', 'Document diffusé.');
    }

    private function creerTransmission(Dossier $dossier, string $mode, StoreDocumentTransmissionRequest $request): DocumentTransmission
    {
        $transmission = DocumentTransmission::create([
            'dossier_id' => $dossier->id,
            'document_version_id' => $dossier->derniereVersion?->id,
            'emetteur_id' => Auth::id(),
            'mode' => $mode,
            'commentaire' => $request->commentaire,
            'date_transmission' => now(),
        ]);

        foreach ($request->structures ?? [] as $structureId) {
            TransmissionDestinataire::create([
                'document_transmission_id' => $transmission->id,
                'structure_id' => $structureId,
            ]);

            foreach (User::where('structure_id', $structureId)->where('actif', true)->get() as $membre) {
                $membre->notify(new DocumentTransmis($transmission));
            }
        }

        foreach ($request->users ?? [] as $userId) {
            TransmissionDestinataire::create([
                'document_transmission_id' => $transmission->id,
                'user_id' => $userId,
            ]);

            User::find($userId)?->notify(new DocumentTransmis($transmission));
        }

        return $transmission;
    }
}
