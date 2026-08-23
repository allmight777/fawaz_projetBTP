<?php

namespace App\Http\Controllers\Structure;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BureauEtudesController extends Controller
{
    /**
     * Dashboard pour le chef du bureau d'études
     */
    public function chefDashboard()
    {
        $user = Auth::user();
        return view('bureau_etudes.chef.dashboard', compact('user'));
    }

    /**
     * Dashboard pour le collaborateur (ingénieur d'études)
     */
    public function collaborateurDashboard()
    {
        $user = Auth::user();
        return view('bureau_etudes.collaborateur.dashboard', compact('user'));
    }

    /**
     * Liste des projets d'études
     */
    public function projets()
    {
        return view('bureau_etudes.projets');
    }

    /**
     * Liste des plans et dessins
     */
    public function plans()
    {
        return view('bureau_etudes.plans');
    }

    /**
     * Liste des notes de calcul
     */
    public function notesCalcul()
    {
        return view('bureau_etudes.notes_calcul');
    }
}
