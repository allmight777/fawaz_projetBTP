<?php

namespace App\Http\Controllers;

use App\Models\DocumentAssignment;
use Illuminate\Http\Request;

class ControleurController extends Controller
{
    /** Tableau de bord du collaborateur d'une structure visante (BC, BE ou MOE) : ses affectations. */
    public function dashboard()
    {
        $user = auth()->user();
        $lot = $user->lot;

        $base = fn () => DocumentAssignment::where('controleur_id', $user->id);

        $totalDocuments = $base()->count();
        $documentsEnAttente = $base()->where('statut', 'en_attente')->count();
        $documentsEnCours = $base()->where('statut', 'en_cours')->count();
        $documentsTermines = $base()->where('statut', 'termine')->count();
        $documentsEnRetard = $base()->where('statut', '!=', 'termine')->whereNotNull('date_limite')->where('date_limite', '<', now())->count();

        $affectationsRecentes = $base()
            ->with('documentVersion.dossier.documentType')
            ->latest()
            ->take(5)
            ->get();

        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->translatedFormat('M'))->toArray();
        $affectationsData = collect(range(5, 0))->map(
            fn ($i) => $base()->whereBetween('created_at', [now()->subMonths($i)->startOfMonth(), now()->subMonths($i)->endOfMonth()])->count()
        )->toArray();

        $statutLabels = ['En attente', 'En cours', 'Terminées'];
        $statutData = [$documentsEnAttente, $documentsEnCours, $documentsTermines];

        return view('controleur.dashboard', compact(
            'user',
            'lot',
            'totalDocuments',
            'documentsEnAttente',
            'documentsEnCours',
            'documentsTermines',
            'documentsEnRetard',
            'affectationsRecentes',
            'statutLabels',
            'statutData',
            'months',
            'affectationsData'
        ));
    }

    public function monLot()
    {
        $lot = auth()->user()->lot;
        return view('controleur.mon-lot', compact('lot'));
    }

    public function taches()
    {
        return view('controleur.taches');
    }
}
