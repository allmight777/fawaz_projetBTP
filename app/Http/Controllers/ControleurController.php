<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControleurController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $lot = $user->lot;

        return view('controleur.dashboard', compact('user', 'lot'));
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
