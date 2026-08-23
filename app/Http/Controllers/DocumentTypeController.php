<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentTypeController extends Controller
{
    /**
     * Affiche la liste des types de documents
     */
    public function index()
    {
        $documentTypes = DocumentType::withCount('checklistItems')
            ->orderBy('nom')
            ->get();

        return view('admin.document-types.index', compact('documentTypes'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        $categories = [
            'etude_execution' => 'Étude d\'exécution',
            'gestion_projet' => 'Gestion de projet',
            'assurance_qualite' => 'Assurance qualité',
            'environnemental_social' => 'Environnemental et social',
            'bc_document' => 'Documents Bureau de Contrôle',
        ];

        $modes = [
            'simple' => 'Informatif (simple)',
            'validation' => 'Validation (contrôle)',
        ];

        return view('admin.document-types.create', compact('categories', 'modes'));
    }

    /**
     * Enregistre un nouveau type de document
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:document_types',
            'nom' => 'required|string|max:255',
            'categorie' => 'required|in:etude_execution,gestion_projet,assurance_qualite,environnemental_social,bc_document',
            'mode_traitement' => 'required|in:simple,validation',
            'actif' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $documentType = DocumentType::create([
            'code' => $request->code,
            'nom' => $request->nom,
            'categorie' => $request->categorie,
            'mode_traitement' => $request->mode_traitement,
            'actif' => $request->actif ?? true,
        ]);

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Type de document créé avec succès.');
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit($id)
    {
        $documentType = DocumentType::with('checklistItems')->findOrFail($id);

        $categories = [
            'etude_execution' => 'Étude d\'exécution',
            'gestion_projet' => 'Gestion de projet',
            'assurance_qualite' => 'Assurance qualité',
            'environnemental_social' => 'Environnemental et social',
            'bc_document' => 'Documents Bureau de Contrôle',
        ];

        $modes = [
            'simple' => 'Informatif (simple)',
            'validation' => 'Validation (contrôle)',
        ];

        return view('admin.document-types.edit', compact('documentType', 'categories', 'modes'));
    }

    /**
     * Met à jour un type de document
     */
    public function update(Request $request, $id)
    {
        $documentType = DocumentType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:document_types,code,' . $id,
            'nom' => 'required|string|max:255',
            'categorie' => 'required|in:etude_execution,gestion_projet,assurance_qualite,environnemental_social,bc_document',
            'mode_traitement' => 'required|in:simple,validation',
            'actif' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $documentType->update([
            'code' => $request->code,
            'nom' => $request->nom,
            'categorie' => $request->categorie,
            'mode_traitement' => $request->mode_traitement,
            'actif' => $request->actif ?? true,
        ]);

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Type de document modifié avec succès.');
    }

    /**
     * Supprime un type de document
     */
    public function destroy($id)
    {
        $documentType = DocumentType::findOrFail($id);

        // Vérifier si des documents utilisent ce type
        if ($documentType->checklistItems()->count() > 0) {
            return redirect()->route('admin.document-types.index')
                ->with('error', 'Impossible de supprimer ce type car il a des critères associés.');
        }

        $documentType->delete();

        return redirect()->route('admin.document-types.index')
            ->with('success', 'Type de document supprimé avec succès.');
    }

    // ============================================================
    // GESTION DES CRITÈRES (CHECKLIST ITEMS)
    // ============================================================

    /**
     * Affiche la liste des critères pour un type de document
     */
    public function checklistIndex($documentTypeId)
    {
        $documentType = DocumentType::with('checklistItems')->findOrFail($documentTypeId);

        return view('admin.document-types.checklist', compact('documentType'));
    }

    /**
     * Ajoute un critère à la checklist
     */
    public function checklistStore(Request $request, $documentTypeId)
    {
        $documentType = DocumentType::findOrFail($documentTypeId);

        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:255',
            'obligatoire' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Trouver le prochain ordre
        $maxOrdre = $documentType->checklistItems()->max('ordre') ?? 0;

        ChecklistItem::create([
            'document_type_id' => $documentType->id,
            'libelle' => $request->libelle,
            'ordre' => $maxOrdre + 1,
            'obligatoire' => $request->obligatoire ?? true,
        ]);

        return redirect()->route('admin.document-types.checklist', $documentType->id)
            ->with('success', 'Critère ajouté avec succès.');
    }

    /**
     * Met à jour un critère
     */
    public function checklistUpdate(Request $request, $documentTypeId, $itemId)
    {
        $item = ChecklistItem::where('document_type_id', $documentTypeId)
            ->findOrFail($itemId);

        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:255',
            'obligatoire' => 'boolean',
            'ordre' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $item->update([
            'libelle' => $request->libelle,
            'obligatoire' => $request->obligatoire ?? true,
            'ordre' => $request->ordre ?? $item->ordre,
        ]);

        return redirect()->route('admin.document-types.checklist', $documentTypeId)
            ->with('success', 'Critère modifié avec succès.');
    }

    /**
     * Supprime un critère
     */
    public function checklistDestroy($documentTypeId, $itemId)
    {
        $item = ChecklistItem::where('document_type_id', $documentTypeId)
            ->findOrFail($itemId);

        $item->delete();

        return redirect()->route('admin.document-types.checklist', $documentTypeId)
            ->with('success', 'Critère supprimé avec succès.');
    }

    /**
     * Réorganise l'ordre des critères
     */
    public function checklistReorder(Request $request, $documentTypeId)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'exists:checklist_items,id',
            'items.*.ordre' => 'integer|min:0',
        ]);

        foreach ($request->items as $itemData) {
            ChecklistItem::where('id', $itemData['id'])
                ->where('document_type_id', $documentTypeId)
                ->update(['ordre' => $itemData['ordre']]);
        }

        return response()->json(['success' => true]);
    }
}
