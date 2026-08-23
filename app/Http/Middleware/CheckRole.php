<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Plusieurs rôles autorisés possibles : role:ADMIN,CHEF LOT
        $rolesAutorises = explode(',', $role);

        if (!in_array(Auth::user()->role, $rolesAutorises, true)) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
