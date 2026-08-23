<?php

namespace App\Http\Controllers\Structure;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Demande;
use App\Models\Lot;

class BureauControleController extends Controller
{
    /**
     * Dashboard pour le chef du bureau de contrôle
     * Réutilise les vues de ChefLot
     */
    public function chefDashboard()
    {
        // Rediriger vers le dashboard du Chef Lot
        return redirect()->route('cheflot.dashboard');
    }

    /**
     * Dashboard pour le collaborateur (contrôleur)
     * Passe toutes les variables nécessaires à la vue
     */
    public function collaborateurDashboard()
    {
        $user = Auth::user();

        // Récupérer le lot de l'utilisateur
        $lot = $user->lot;

        // Statistiques - avec vérification si lot existe
        $totalDemandes = $lot ? Demande::where('lot_id', $lot->id)->count() : 0;
        $demandesEnAttente = $lot ? Demande::where('lot_id', $lot->id)->where('statut', 'EN ATTENTE')->count() : 0;
        $demandesEnControle = $lot ? Demande::where('lot_id', $lot->id)->where('statut', 'EN CONTROLE')->count() : 0;
        $demandesValidees = $lot ? Demande::where('lot_id', $lot->id)->where('statut', 'VALIDE')->count() : 0;
        $demandesRejetees = $lot ? Demande::where('lot_id', $lot->id)->where('statut', 'REJETE')->count() : 0;
        $demandesEnRetard = $lot ? Demande::where('lot_id', $lot->id)
            ->where('statut', '!=', 'VALIDE')
            ->where('created_at', '<', now()->subDays(7))
            ->count() : 0;

        // Demandes récentes
        $demandesRecentes = $lot ? Demande::where('lot_id', $lot->id)
            ->with('soumisPar')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get() : collect();

        // ✅ Données pour les graphiques (6 derniers mois)
        $months = [];
        $documentsData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M Y');
            $documentsData[] = $lot ? Demande::where('lot_id', $lot->id)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count() : 0;
        }

        // Données pour le graphique des priorités
        $priorites = [
            'Basse' => $lot ? Demande::where('lot_id', $lot->id)->where('priorite', 'Basse')->count() : 0,
            'Moyenne' => $lot ? Demande::where('lot_id', $lot->id)->where('priorite', 'Moyenne')->count() : 0,
            'Haute' => $lot ? Demande::where('lot_id', $lot->id)->where('priorite', 'Haute')->count() : 0,
            'Urgente' => $lot ? Demande::where('lot_id', $lot->id)->where('priorite', 'Urgente')->count() : 0,
        ];

        // ✅ ALIAS : Renommer les variables pour correspondre à la vue
        $totalDocuments = $totalDemandes;
        $documentsEnAttente = $demandesEnAttente;
        $documentsEnCours = $demandesEnControle;
        $documentsValides = $demandesValidees;
        $documentsACorriger = $demandesRejetees;
        $documentsEnAnalyse = 0; // Pas de statut correspondant
        $documentsRecents = $demandesRecentes;

        // ✅ Statuts pour le graphique camembert
        $statutLabels = ['En attente', 'En cours', 'Validés', 'À corriger'];
        $statutData = [
            $demandesEnAttente,
            $demandesEnControle,
            $demandesValidees,
            $demandesRejetees,
        ];

        // ✅ Retourner la vue avec TOUTES les variables
        return view('controleur.dashboard', compact(
            'user',
            'lot',
            // Variables pour les cartes de stats
            'totalDocuments',
            'documentsEnAttente',
            'documentsEnCours',
            'documentsValides',
            'documentsACorriger',
            'documentsEnAnalyse',
            // Variables pour le tableau
            'documentsRecents',
            // Variables pour les graphiques
            'statutLabels',
            'statutData',
            'months',
            'documentsData',  // ✅ AJOUTÉ
            'priorites'
        ));
    }

    /**
     * Liste des contrôles en cours
     */
    public function controles()
    {
        $user = Auth::user();
        $lot = $user->lot;

        $demandes = $lot ? Demande::where('lot_id', $lot->id)
            ->where('statut', 'EN CONTROLE')
            ->with('soumisPar')
            ->orderBy('created_at', 'desc')
            ->get() : collect();

        return view('bureau_controle.controles', compact('demandes'));
    }

    /**
     * Liste des rapports
     */
    public function rapports()
    {
        $user = Auth::user();
        $lot = $user->lot;

        $demandes = $lot ? Demande::where('lot_id', $lot->id)
            ->where('statut', 'VALIDE')
            ->with('soumisPar')
            ->orderBy('created_at', 'desc')
            ->get() : collect();

        return view('bureau_controle.rapports', compact('demandes'));
    }

    /**
     * Liste des non-conformités
     */
    public function nonConformites()
    {
        $user = Auth::user();
        $lot = $user->lot;

        $demandes = $lot ? Demande::where('lot_id', $lot->id)
            ->where('statut', 'REJETE')
            ->with('soumisPar')
            ->orderBy('created_at', 'desc')
            ->get() : collect();

        return view('bureau_controle.non_conformites', compact('demandes'));
    }
}
