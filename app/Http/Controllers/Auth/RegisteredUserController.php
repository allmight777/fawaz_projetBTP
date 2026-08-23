<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Structure;
use App\Models\Lot;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $structures = Structure::where('actif', true)->orderBy('nom')->get();
        $lots = Lot::where('actif', true)->orderBy('code')->get();
        return view('auth.register', compact('structures', 'lots'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'structure_id' => ['required', 'exists:structures,id'],
            'categorie_role' => ['required', 'in:responsable_organisme,collaborateur'],
            'fonction' => ['nullable', 'string', 'max:255'],
            'specialite' => ['nullable', 'string', 'max:255'],
            'lot_id' => ['nullable', 'exists:lots,id'],
        ]);

        $structure = Structure::find($request->structure_id);
        $role = $this->determineRole($structure, $request->categorie_role);

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'actif' => false,
            'structure_id' => $request->structure_id,
            'categorie_role' => $request->categorie_role,
            'fonction' => $request->fonction,
            'specialite' => $request->specialite,
            'lot_id' => $request->lot_id,
        ]);

        event(new Registered($user));

        return redirect()->route('login')
            ->with('status', 'Votre compte a été créé. Un administrateur doit l\'activer avant que vous puissiez vous connecter.');
    }

    /**
     * Détermine le rôle en fonction de la structure et de la catégorie
     */
    private function determineRole(Structure $structure, string $categorieRole): string
    {
        if ($categorieRole === 'responsable_organisme') {
            return match ($structure->type) {
                'entreprise' => User::ROLE_ENTREPRISE_CHEF,
                'bureau_controle' => User::ROLE_BUREAU_CONTROLE_CHEF,
                'bureau_etudes' => User::ROLE_BUREAU_ETUDES_CHEF,
                'maitre_ouvrage' => User::ROLE_MAITRE_OUVRAGE_CHEF,
                default => User::ROLE_CONTROLEUR,
            };
        }

        return match ($structure->type) {
            'entreprise' => User::ROLE_ENTREPRISE_COLLABORATEUR,
            'bureau_controle' => User::ROLE_BUREAU_CONTROLE_COLLABORATEUR,
            'bureau_etudes' => User::ROLE_BUREAU_ETUDES_COLLABORATEUR,
            'maitre_ouvrage' => User::ROLE_MAITRE_OUVRAGE_COLLABORATEUR,
            default => User::ROLE_CONTROLEUR,
        };
    }
}
