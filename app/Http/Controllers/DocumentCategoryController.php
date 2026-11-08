<?php

namespace App\Http\Controllers;

use App\Models\DocumentCategory;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Gestion des catégories de types de documents (admin). Le nom et l'ordre sont modifiables à tout moment. */
class DocumentCategoryController extends Controller
{
    public function index()
    {
        $categories = DocumentCategory::orderBy('ordre')->orderBy('nom')->get();
        $comptes = DocumentType::selectRaw('categorie, count(*) as n')->groupBy('categorie')->pluck('n', 'categorie');

        return view('admin.document-categories.index', compact('categories', 'comptes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:120',
        ], ['nom.required' => 'Indiquez le nom de la catégorie.']);

        $cle = str_replace('-', '_', Str::slug($data['nom']));
        if ($cle === '' || DocumentCategory::where('cle', $cle)->exists()) {
            return back()->withErrors(['nom' => 'Une catégorie de ce nom existe déjà.'])->withInput();
        }

        DocumentCategory::create(['cle' => $cle, 'nom' => $data['nom'], 'ordre' => (int) DocumentCategory::max('ordre') + 1]);

        return back()->with('success', 'Catégorie créée.');
    }

    public function update(Request $request, DocumentCategory $categorie)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:120', Rule::unique('document_categories', 'nom')->ignore($categorie->id)],
            'ordre' => 'nullable|integer|min:0|max:9999',
        ], ['nom.unique' => 'Une catégorie porte déjà ce nom.', 'nom.required' => 'Le nom ne peut pas être vide.']);

        // La clé interne ne change pas : les types déjà rattachés restent liés.
        $categorie->update(['nom' => $data['nom'], 'ordre' => $data['ordre'] ?? $categorie->ordre]);

        return back()->with('success', 'Catégorie modifiée.');
    }

    public function destroy(DocumentCategory $categorie)
    {
        if (DocumentType::where('categorie', $categorie->cle)->exists()) {
            return back()->with('error', 'Cette catégorie contient encore des types de documents : déplacez-les ou supprimez-les d\'abord.');
        }

        $categorie->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }
}
