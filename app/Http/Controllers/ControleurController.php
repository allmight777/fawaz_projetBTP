<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControleurController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $lot = $user->lot;

        $demandesQuery = $lot ? $lot->demandes() : \App\Models\Demande::whereRaw('1=0');

        // Statistiques avec les noms de variables attendus par la vue
        $totalDemandes = (clone $demandesQuery)->count();
        $demandesEnAttente = (clone $demandesQuery)->where('statut', 'EN ATTENTE')->count();
        $demandesEnControle = (clone $demandesQuery)->where('statut', 'EN CONTROLE')->count();
        $demandesValidees = (clone $demandesQuery)->where('statut', 'VALIDE')->count();
        $demandesRejetees = (clone $demandesQuery)->where('statut', 'REJETE')->count();
        $demandesEnRetard = (clone $demandesQuery)
            ->where('statut', '!=', 'VALIDE')
            ->where('created_at', '<', now()->subDays(7))
            ->count();

        $demandesRecentes = (clone $demandesQuery)->latest()->take(5)->get();

        // Données pour le graphique d'évolution (6 derniers mois)
        $months = collect(range(5, 0))->map(fn($i) => now()->subMonths($i)->translatedFormat('M'))->toArray();
        $documentsData = collect(range(5, 0))->map(function ($i) use ($demandesQuery) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = now()->subMonths($i)->endOfMonth();
            return (clone $demandesQuery)->whereBetween('created_at', [$start, $end])->count();
        })->toArray();

        $priorites = [
            'Basse' => (clone $demandesQuery)->where('priorite', 'Basse')->count(),
            'Moyenne' => (clone $demandesQuery)->where('priorite', 'Moyenne')->count(),
            'Haute' => (clone $demandesQuery)->where('priorite', 'Haute')->count(),
            'Urgente' => (clone $demandesQuery)->where('priorite', 'Urgente')->count(),
        ];

        // ✅ ALIAS pour correspondre aux attentes de la vue (statistiques)
        $totalDocuments = $totalDemandes;
        $documentsEnAttente = $demandesEnAttente;
        $documentsEnCours = $demandesEnControle;
        $documentsValides = $demandesValidees;
        $documentsACorriger = $demandesRejetees;
        $documentsEnAnalyse = 0;
        $documentsRecents = $demandesRecentes;

        // Statuts pour le graphique camembert
        $statutLabels = ['En attente', 'En cours', 'Validés', 'À corriger'];
        $statutData = [
            $demandesEnAttente,
            $demandesEnControle,
            $demandesValidees,
            $demandesRejetees,
        ];

        return view('controleur.dashboard', compact(
            'user',
            'lot',
            // Variables pour la vue (alias)
            'totalDocuments',
            'documentsEnAttente',
            'documentsEnCours',
            'documentsValides',
            'documentsACorriger',
            'documentsEnAnalyse',
            'documentsRecents',
            'statutLabels',
            'statutData',
            // Données pour les graphiques
            'months',
            'documentsData',  // ✅ AJOUTÉ : utilisé par le graphique d'évolution
            'priorites'
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
