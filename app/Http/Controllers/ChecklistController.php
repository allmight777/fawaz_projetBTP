<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DocumentChecklistResponse;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\User;
use App\Notifications\DocumentRecu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChecklistController extends Controller
{
    // Retourne les items de checklist d'un type de document (utilisé en AJAX depuis le formulaire d'import)
    public function parType(DocumentType $documentType)
    {
        return response()->json(
            $documentType->checklistItems()->orderBy('ordre')->get(['id', 'libelle', 'obligatoire'])
        );
    }

    public function show(DocumentVersion $documentVersion)
    {
        abort_unless($documentVersion->dossier->cree_par === Auth::id(), 403, 'Seul l\'émetteur du dossier peut compléter cette checklist.');

        $documentVersion->load('dossier.documentType.checklistItems', 'checklistReponses');

        $items = $documentVersion->dossier->documentType->checklistItems;
        $reponses = $documentVersion->checklistReponses->keyBy('checklist_item_id');

        return view('dossiers.checklist', compact('documentVersion', 'items', 'reponses'));
    }

    public function store(Request $request, DocumentVersion $documentVersion)
    {
        abort_unless($documentVersion->dossier->cree_par === Auth::id(), 403, 'Seul l\'émetteur du dossier peut compléter cette checklist.');

        $items = $documentVersion->dossier->documentType->checklistItems;

        $request->validate([
            'reponses' => 'array',
        ]);

        $manquants = [];

        foreach ($items as $item) {
            $coche = $request->boolean('reponses.' . $item->id . '.coche');
            $commentaire = $request->input('reponses.' . $item->id . '.commentaire');

            DocumentChecklistResponse::updateOrCreate(
                ['document_version_id' => $documentVersion->id, 'checklist_item_id' => $item->id],
                ['coche' => $coche, 'commentaire' => $commentaire]
            );

            if ($item->obligatoire && !$coche) {
                $manquants[] = $item->libelle;
            }
        }

        if (!empty($manquants)) {
            return redirect()->back()
                ->with('error', 'Items obligatoires non cochés : ' . implode(', ', $manquants));
        }

        $documentVersion->update(['statut' => 'soumis']);
        $documentVersion->dossier->update(['statut' => 'soumis']);

        AuditLog::enregistrer('CHECKLIST_COMPLETEE', $documentVersion->dossier_id, [
            'document_version_id' => $documentVersion->id,
        ]);

        $responsablesBC = User::where('role', 'CHEF LOT')->where('actif', true)->get();
        foreach ($responsablesBC as $responsable) {
            $responsable->notify(new DocumentRecu($documentVersion->dossier));
        }

        return redirect()->route('dossiers.show', $documentVersion->dossier_id)
            ->with('success', 'Checklist validée. Le document est soumis au Bureau de Contrôle.');
    }
}
