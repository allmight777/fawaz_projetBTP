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
     * Dashboard pour le collaborateur (contrôleur) : même tableau de bord que l'espace contrôleur.
     */
    public function collaborateurDashboard()
    {
        return redirect()->route('controleur.dashboard');
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
