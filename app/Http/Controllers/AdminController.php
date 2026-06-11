<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        // Statistiques de base
        $totalUsers = User::count();
        $totalLots = Lot::count();
        $totalControleurs = User::where('role', 'CONTROLEUR')->count();
        $usersActifs = User::where('actif', true)->count();

        // Données pour les graphiques
        $selectedYear = $request->get('year', date('Y'));
        $availableYears = range(2023, date('Y'));

        // 1. Répartition des utilisateurs par rôle
        $roleLabels = ['ADMIN', 'CHEF LOT', 'CONTROLEUR'];
        $roleData = [
            User::where('role', 'ADMIN')->count(),
            User::where('role', 'CHEF LOT')->count(),
            User::where('role', 'CONTROLEUR')->count()
        ];

        // 2. Répartition des contrôleurs par lot
        $lotLabels = Lot::where('actif', true)->pluck('nom')->toArray();
        $lotData = [];
        foreach (Lot::where('actif', true)->get() as $lot) {
            $lotData[] = User::where('role', 'CONTROLEUR')->where('lot_id', $lot->id)->where('actif', true)->count();
        }

        // 3. Statut des utilisateurs (Actifs/Inactifs)
        $statusData = [
            User::where('actif', true)->count(),
            User::where('actif', false)->count()
        ];

        // 4. Évolution des inscriptions par mois (année sélectionnée)
        $monthlyData = [];
        $monthlyLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = User::whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $i)
                ->count();
        }

        // 5. Répartition par lot (pour graphique camembert)
        $lotPieLabels = [];
        $lotPieData = [];
        foreach (Lot::all() as $lot) {
            $lotPieLabels[] = $lot->nom;
            $lotPieData[] = User::where('lot_id', $lot->id)->count();
        }

        // 6. Taux d'activité par lot
        $lotActivityLabels = [];
        $lotActivityData = [];
        foreach (Lot::all() as $lot) {
            $lotActivityLabels[] = $lot->nom;
            $totalUsersLot = User::where('lot_id', $lot->id)->count();
            $activeUsersLot = User::where('lot_id', $lot->id)->where('actif', true)->count();
            $lotActivityData[] = $totalUsersLot > 0 ? round(($activeUsersLot / $totalUsersLot) * 100, 1) : 0;
        }

        // Récupérer les utilisateurs pour le tableau
        $users = User::with('lot')->latest()->paginate(10);

        return view('admin.dashboard', compact(
            'totalUsers', 'totalLots', 'totalControleurs', 'usersActifs',
            'roleLabels', 'roleData', 'lotLabels', 'lotData', 'statusData',
            'monthlyData', 'monthlyLabels', 'selectedYear', 'availableYears',
            'lotPieLabels', 'lotPieData', 'lotActivityLabels', 'lotActivityData',
            'users'
        ));
    }

    public function users()
    {
        $users = User::with('lot')->get();
        return view('admin.users.users', compact('users'));
    }

    public function createUser()
    {
        $lots = Lot::all();
        return view('admin.users.users-create', compact('lots'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:ADMIN,CHEF LOT,CONTROLEUR',
            'lot_id' => 'nullable|exists:lots,id',
            'actif' => 'boolean',
        ]);

        User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make('password'),
            'role' => $request->role,
            'lot_id' => $request->lot_id,
            'actif' => $request->actif ?? true,
        ]);

        return redirect()->route('admin.users')->with('success', 'Utilisateur créé avec succès.');
    }

    public function editUser(User $user)
    {
        $lots = Lot::all();
        return view('admin.users.users-edit', compact('user', 'lots'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:ADMIN,CHEF LOT,CONTROLEUR',
            'lot_id' => 'nullable|exists:lots,id',
            'actif' => 'boolean',
        ]);

        $user->update($request->all());

        return redirect()->route('admin.users')->with('success', 'Utilisateur modifié avec succès.');
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Utilisateur supprimé.');
    }

    public function lots()
    {
        $lots = Lot::with('users')->get();
        return view('admin.lots.lots', compact('lots'));
    }

    public function createLot()
    {
        return view('admin.lots.lots-create');
    }

    public function storeLot(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|unique:lots',
            'description' => 'nullable|string',
            'actif' => 'boolean',
        ]);

        Lot::create([
            'nom' => $request->nom,
            'code' => $request->code,
            'description' => $request->description,
            'actif' => $request->actif ?? true,
        ]);

        return redirect()->route('admin.lots')->with('success', 'Lot créé avec succès.');
    }

    public function editLot(Lot $lot)
    {
        return view('admin.lots.lots-edit', compact('lot'));
    }

    public function updateLot(Request $request, Lot $lot)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|unique:lots,code,' . $lot->id,
            'description' => 'nullable|string',
            'actif' => 'boolean',
        ]);

        $lot->update($request->all());

        return redirect()->route('admin.lots')->with('success', 'Lot modifié avec succès.');
    }

    public function destroyLot(Lot $lot)
    {
        // Vérifier si des utilisateurs sont affectés à ce lot
        if ($lot->users()->count() > 0) {
            return redirect()->route('admin.lots')->with('error', 'Impossible de supprimer ce lot car des utilisateurs y sont affectés.');
        }

        $lot->delete();
        return redirect()->route('admin.lots')->with('success', 'Lot supprimé avec succès.');
    }
}
