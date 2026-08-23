<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentObservationRequest;
use App\Models\AuditLog;
use App\Models\DocumentAssignment;
use App\Models\DocumentObservation;
use Illuminate\Support\Facades\Auth;

class DocumentObservationController extends Controller
{
    public function store(StoreDocumentObservationRequest $request, DocumentAssignment $documentAssignment)
    {
        if ($documentAssignment->controleur_id !== Auth::id()) {
            abort(403);
        }

        DocumentObservation::create([
            'document_assignment_id' => $documentAssignment->id,
            'auteur_id' => Auth::id(),
            'type' => $request->type,
            'contenu' => $request->contenu,
        ]);

        if ($documentAssignment->statut === 'en_attente') {
            $documentAssignment->update(['statut' => 'en_cours']);
        }

        AuditLog::enregistrer('OBSERVATION', $documentAssignment->documentVersion->dossier_id, [
            'document_assignment_id' => $documentAssignment->id,
        ]);

        return redirect()->back()->with('success', 'Observation enregistrée.');
    }

    // Le responsable du Bureau de Contrôle retient (ou non) une observation lors de la consolidation
    public function toggleRetenue(DocumentObservation $documentObservation)
    {
        $documentObservation->update(['retenue' => !$documentObservation->retenue]);

        return redirect()->back();
    }
}
