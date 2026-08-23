<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'layout' => $this->layoutFor($request->user()->role),
            'theme' => $this->colorFor($request->user()->role),
        ]);
    }

    /**
     * Layout propre à chaque rôle, pour que "Mon profil" reste dans l'espace
     * de l'utilisateur connecté plutôt que d'afficher le layout admin à tout le monde.
     */
    private function layoutFor(?string $role): string
    {
        return match ($role) {
            'ADMIN' => 'layouts.admin',
            'ENTREPRISE_CHEF', 'ENTREPRISE_COLLABORATEUR' => 'layouts.entreprise',
            'CHEF LOT', 'BUREAU_CONTROLE_CHEF' => 'layouts.cheflot',
            'CONTROLEUR', 'BUREAU_CONTROLE_COLLABORATEUR' => 'layouts.controleur',
            'MAITRE_OUVRAGE_CHEF', 'MAITRE_OUVRAGE_COLLABORATEUR' => 'layouts.maitre-ouvrage',
            default => 'layouts.app',
        };
    }

    /**
     * Couleurs d'accent de la page profil (dégradé + halo au focus), alignées
     * sur celles du layout de chaque rôle plutôt que sur l'orange admin fixe.
     * profile/edit.blade.php interpole ces valeurs directement dans son bloc
     * <style> (le fichier utilise du CSS avec des hex, pas des classes Tailwind).
     */
    private function colorFor(?string $role): array
    {
        [$primary, $secondary] = match (true) {
            $role === 'ADMIN' => ['#ff8c00', '#ff6b00'],
            in_array($role, ['CHEF LOT', 'CONTROLEUR', 'BUREAU_CONTROLE_CHEF', 'BUREAU_CONTROLE_COLLABORATEUR'], true) => ['#047857', '#065f46'],
            in_array($role, ['ENTREPRISE_CHEF', 'ENTREPRISE_COLLABORATEUR'], true) => ['#2563eb', '#1a4fc4'],
            in_array($role, ['MAITRE_OUVRAGE_CHEF', 'MAITRE_OUVRAGE_COLLABORATEUR'], true) => ['#E91E8C', '#AD1457'],
            in_array($role, ['BUREAU_ETUDES_CHEF', 'BUREAU_ETUDES_COLLABORATEUR'], true) => ['#4f46e5', '#4338ca'],
            default => ['#ff8c00', '#ff6b00'],
        };

        [$r, $g, $b] = sscanf($primary, '#%02x%02x%02x');

        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'glow' => "rgba($r,$g,$b,0.1)",
        ];
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
