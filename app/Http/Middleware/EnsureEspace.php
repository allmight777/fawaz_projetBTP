<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garde d'espace : espace:cheflot,controleur  → seuls ces espaces passent ;
 *                  categorie:chef | collaborateur → distingue le chef du collaborateur.
 * Un utilisateur qui frappe à la mauvaise porte est renvoyé vers SON tableau de bord
 * (jamais dans l'espace d'un autre rôle).
 */
class EnsureEspace
{
    public function handle(Request $request, Closure $next, string $mode, string ...$valeurs): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $autorise = match ($mode) {
            'espace' => in_array($user->espace(), $valeurs, true),
            'categorie' => in_array($user->peutViser() ? 'chef' : 'collaborateur', $valeurs, true),
            default => false,
        };

        if ($autorise) {
            return $next($request);
        }

        $cible = $user->getDashboardRoute();
        // Évite toute boucle de redirection si le tableau de bord est lui-même refusé.
        abort_if($request->routeIs($cible) || $cible === 'dashboard' && $request->routeIs('dashboard'), 403, 'Accès non autorisé pour votre espace.');

        return redirect()->route($cible)->with('error', 'Cette page n\'appartient pas à votre espace.');
    }
}
