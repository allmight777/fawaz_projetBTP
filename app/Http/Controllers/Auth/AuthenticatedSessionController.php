<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // Vérifier si l'utilisateur est actif
        if (!$user->actif) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Votre compte est désactivé. Veuillez contacter l\'administrateur.',
            ]);
        }

        // Vérifier si l'utilisateur est en attente de validation
        if ($user->role === User::ROLE_EN_ATTENTE) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Votre compte est en attente de validation. Un administrateur doit l\'activer.',
            ]);
        }

        // Redirection basée sur le rôle - Utiliser la méthode du modèle
        $routeName = $user->getDashboardRoute();

        // Log pour déboguer
        \Log::info('Redirection vers:', ['route' => $routeName, 'user_id' => $user->id, 'role' => $user->role]);

        return redirect()->route($routeName);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
