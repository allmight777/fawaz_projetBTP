<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;

/** Canevas : consultation (lecture seule) des types de documents et de leurs listes de contrôle. */
class CanevasController extends Controller
{
    public function index()
    {
        $types = DocumentType::where('actif', true)
            ->with(['checklistItems' => fn ($q) => $q->orderBy('ordre')->orderBy('id')])
            ->orderBy('nom')
            ->get();

        $categories = DocumentType::categories();
        $ordre = array_keys($categories);
        $groupes = $types->groupBy('categorie')->sortBy(fn ($t, $cle) => ($r = array_search($cle, $ordre, true)) === false ? 999 : $r);

        return view('canevas.index', compact('groupes', 'categories', 'types'));
    }
}
