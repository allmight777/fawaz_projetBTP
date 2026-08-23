<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lot;
use App\Models\Structure;
use App\Mail\AccountActivatedMail;
use App\Mail\AccountRejectedMail;
use App\Mail\AccountSuspendedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        // Statistiques de base
        $totalUsers = User::count();
        $totalLots = Lot::count();
        $totalStructures = Structure::count();
        $totalControleurs = User::where('role', 'CONTROLEUR')->count();
        $usersActifs = User::where('actif', true)->count();

        // Données pour les graphiques
        $selectedYear = $request->get('year', date('Y'));
        $availableYears = range(2023, date('Y'));

        // 1. Répartition des utilisateurs par rôle
        $roleLabels = ['ADMIN', 'CHEF LOT', 'CONTROLEUR', 'EN_ATTENTE'];
        $roleData = [
            User::where('role', 'ADMIN')->count(),
            User::where('role', 'CHEF LOT')->count(),
            User::where('role', 'CONTROLEUR')->count(),
            User::where('role', 'EN_ATTENTE')->count()
        ];

        // 2. Répartition par structure
        $structureLabels = Structure::where('actif', true)->pluck('nom')->toArray();
        $structureData = [];
        foreach (Structure::where('actif', true)->get() as $structure) {
            $structureData[] = User::where('structure_id', $structure->id)->count();
        }

        // 3. Statut des utilisateurs
        $statusData = [
            User::where('statut', User::STATUT_ACTIF)->where('role', '!=', 'EN_ATTENTE')->count(),
            User::where('statut', User::STATUT_INACTIF)->where('role', '!=', 'EN_ATTENTE')->count(),
            User::where('statut', User::STATUT_DESACTIVE)->where('role', '!=', 'EN_ATTENTE')->count(),
            User::where('role', 'EN_ATTENTE')->count()
        ];
        $statusLabels = ['Actifs', 'Inactifs', 'Désactivés', 'En attente'];

        // 4. Évolution des inscriptions par mois
        $monthlyData = [];
        $monthlyLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = User::whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $i)
                ->count();
        }

        // 5. Répartition par catégorie de rôle
        $categorieLabels = ['Responsable organisme', 'Collaborateur'];
        $categorieData = [
            User::where('categorie_role', 'responsable_organisme')->count(),
            User::where('categorie_role', 'collaborateur')->count()
        ];

        // 6. Taux d'activité par structure
        $structureActivityLabels = [];
        $structureActivityData = [];
        foreach (Structure::where('actif', true)->get() as $structure) {
            $structureActivityLabels[] = $structure->nom;
            $totalUsersStruct = User::where('structure_id', $structure->id)->count();
            $activeUsersStruct = User::where('structure_id', $structure->id)
                ->where('actif', true)
                ->where('role', '!=', 'EN_ATTENTE')
                ->count();
            $structureActivityData[] = $totalUsersStruct > 0 ? round(($activeUsersStruct / $totalUsersStruct) * 100, 1) : 0;
        }

        // Variables pour les graphiques de lots
        $lotPieLabels = [];
        $lotPieData = [];
        foreach (Lot::where('actif', true)->get() as $lot) {
            $lotPieLabels[] = $lot->nom;
            $lotPieData[] = User::where('lot_id', $lot->id)->count();
        }

        $lotLabels = [];
        $lotData = [];
        foreach (Lot::where('actif', true)->get() as $lot) {
            $lotLabels[] = $lot->nom;
            $lotData[] = User::where('role', 'CONTROLEUR')->where('lot_id', $lot->id)->where('actif', true)->count();
        }

        $lotActivityLabels = [];
        $lotActivityData = [];
        foreach (Lot::where('actif', true)->get() as $lot) {
            $lotActivityLabels[] = $lot->nom;
            $totalUsersLot = User::where('lot_id', $lot->id)->count();
            $activeUsersLot = User::where('lot_id', $lot->id)->where('actif', true)->where('role', '!=', 'EN_ATTENTE')->count();
            $lotActivityData[] = $totalUsersLot > 0 ? round(($activeUsersLot / $totalUsersLot) * 100, 1) : 0;
        }

        // Récupérer les utilisateurs pour le tableau
        $users = User::with(['lot', 'structure'])->latest()->paginate(10);

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalLots',
            'totalStructures',
            'totalControleurs',
            'usersActifs',
            'roleLabels',
            'roleData',
            'structureLabels',
            'structureData',
            'statusData',
            'statusLabels',
            'monthlyData',
            'monthlyLabels',
            'selectedYear',
            'availableYears',
            'categorieLabels',
            'categorieData',
            'structureActivityLabels',
            'structureActivityData',
            'lotPieLabels',
            'lotPieData',
            'lotLabels',
            'lotData',
            'lotActivityLabels',
            'lotActivityData',
            'users'
        ));
    }

    public function users()
    {
        $users = User::with(['lot', 'structure'])->get();
        return view('admin.users.users', compact('users'));
    }

    public function createUser()
    {
        $lots = Lot::where('actif', true)->get();
        $structures = Structure::where('actif', true)->orderBy('nom')->get();
        return view('admin.users.users-create', compact('lots', 'structures'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users',
            'structure_id' => 'required|exists:structures,id',
            'categorie_role' => 'required|in:responsable_organisme,collaborateur',
            'fonction' => 'nullable|string|max:255',
            'specialite' => 'nullable|string|max:255',
            'lot_id' => 'nullable|exists:lots,id',
            'actif' => 'boolean',
        ]);

        // Déterminer le rôle en fonction de la structure et de la catégorie
        $structure = Structure::find($request->structure_id);
        $role = $this->determineRole($structure, $request->categorie_role);

        User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make('password'),
            'structure_id' => $request->structure_id,
            'categorie_role' => $request->categorie_role,
            'fonction' => $request->fonction,
            'specialite' => $request->specialite,
            'role' => $role,
            'lot_id' => $request->lot_id,
            'actif' => $request->actif ?? true,
            'statut' => ($request->actif ?? true) ? User::STATUT_ACTIF : User::STATUT_INACTIF,
        ]);

        return redirect()->route('admin.users')->with('success', 'Utilisateur créé avec succès.');
    }

    public function editUser(User $user)
    {
        $lots = Lot::where('actif', true)->get();
        $structures = Structure::where('actif', true)->orderBy('nom')->get();
        return view('admin.users.users-edit', compact('user', 'lots', 'structures'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'structure_id' => 'required|exists:structures,id',
            'categorie_role' => 'required|in:responsable_organisme,collaborateur',
            'fonction' => 'nullable|string|max:255',
            'specialite' => 'nullable|string|max:255',
            'lot_id' => 'nullable|exists:lots,id',
            'statut' => 'required|in:actif,inactif,desactive,rejected',
            'raison' => 'required_if:statut,inactif,desactive,rejected|nullable|string|max:1000',
        ]);

        // Récupérer l'ancien statut
        $oldStatut = $user->statut;
        $oldActif = $user->actif;
        $oldRole = $user->role;

        // Déterminer le nouveau statut
        $newStatut = $request->statut;
        $newActif = false;
        $isRejected = false;
        $isActivated = false;

        if ($request->statut === 'actif') {
            $newActif = true;
            if ($oldRole === User::ROLE_EN_ATTENTE || !$oldActif) {
                $isActivated = true;
            }
        } elseif (in_array($request->statut, ['inactif', 'desactive'], true)) {
            $newActif = false;
        } elseif ($request->statut === 'rejected') {
            $newActif = false;
            $newStatut = 'inactif';
            $isRejected = true;
        }

     // Déterminer le rôle selon la structure et la catégorie de rôle actuelles.
// On recalcule TOUJOURS le rôle à partir du formulaire (structure_id + categorie_role),
// pour que role et categorie_role ne puissent jamais être désynchronisés.
$structure = Structure::find($request->structure_id);
$newRole = $this->determineRole($structure, $request->categorie_role);

// Si le compte est refusé, on le met en EN_ATTENTE (prioritaire)
if ($isRejected) {
    $newRole = User::ROLE_EN_ATTENTE;
    $newActif = false;
}

// Si le compte était encore EN_ATTENTE et qu'on ne l'active pas maintenant,
// on le laisse EN_ATTENTE (pas de rôle "métier" tant qu'il n'est pas validé)
if (!$isActivated && !$isRejected && $oldRole === User::ROLE_EN_ATTENTE) {
    $newRole = User::ROLE_EN_ATTENTE;
}

        $user->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'structure_id' => $request->structure_id,
            'categorie_role' => $request->categorie_role,
            'fonction' => $request->fonction,
            'specialite' => $request->specialite,
            'role' => $newRole,
            'lot_id' => $request->lot_id,
            'actif' => $newActif,
            'statut' => $newStatut,
            'raison' => $request->raison,
        ]);

        $user->refresh();

        $this->sendStatusEmail($user, $oldActif, $oldRole, $request->raison, $isRejected, $isActivated);

        return redirect()->route('admin.users')->with('success', 'Utilisateur modifié avec succès.');
    }

    /**
     * Envoie un email à l'utilisateur en fonction du changement de statut
     */
    private function sendStatusEmail(User $user, bool $oldActif, string $oldRole, ?string $raison, bool $isRejected, bool $isActivated): void
    {
        Log::info('Envoi email - Statut complet:', [
            'user' => $user->email,
            'oldRole' => $oldRole,
            'oldActif' => $oldActif,
            'newRole' => $user->role,
            'newActif' => $user->actif,
            'isRejected' => $isRejected,
            'isActivated' => $isActivated,
            'raison' => $raison,
        ]);

        if ($isActivated && $user->actif && $user->role !== User::ROLE_EN_ATTENTE) {
            Log::info('>>> ENVOI EMAIL D\'ACTIVATION à ' . $user->email);
            try {
                Mail::to($user->email)->send(new AccountActivatedMail($user, $raison ?? ''));
                Log::info('Email d\'activation envoyé avec succès à ' . $user->email);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email activation: ' . $e->getMessage());
            }
            return;
        }

        if ($isRejected && $oldRole === User::ROLE_EN_ATTENTE) {
            Log::info('>>> ENVOI EMAIL DE REFUS à ' . $user->email);
            try {
                Mail::to($user->email)->send(new AccountRejectedMail($user, $raison ?? 'Aucune raison spécifiée.'));
                Log::info('Email de refus envoyé avec succès à ' . $user->email);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email refus: ' . $e->getMessage());
            }
            return;
        }

        if (!$oldActif && $user->actif && $oldRole !== User::ROLE_EN_ATTENTE) {
            Log::info('>>> ENVOI EMAIL DE RÉACTIVATION à ' . $user->email);
            try {
                Mail::to($user->email)->send(new AccountActivatedMail($user, $raison ?? ''));
                Log::info('Email de réactivation envoyé avec succès à ' . $user->email);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email réactivation: ' . $e->getMessage());
            }
            return;
        }

        if ($oldActif && !$user->actif && $oldRole !== User::ROLE_EN_ATTENTE) {
            if ($user->statut === User::STATUT_INACTIF) {
                Log::info('>>> ENVOI EMAIL DE SUSPENSION TEMPORAIRE à ' . $user->email);
                try {
                    Mail::to($user->email)->send(new AccountSuspendedMail($user, $raison ?? ''));
                    Log::info('Email de suspension envoyé avec succès à ' . $user->email);
                } catch (\Exception $e) {
                    Log::error('Erreur envoi email suspension: ' . $e->getMessage());
                }
            } else {
                Log::info('Désactivation définitive du compte de ' . $user->email . ' - Aucun email envoyé');
            }
            return;
        }

        Log::info('Aucun email envoyé pour ' . $user->email . ' - Aucune condition remplie');
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Détermine le rôle en fonction de la structure et de la catégorie
     * UTILISE LES CONSTANTES DE LA CLASSE USER
     */
    private function determineRole(Structure $structure, string $categorieRole): string
    {
        if ($categorieRole === 'responsable_organisme') {
            switch ($structure->type) {
                case 'entreprise':
                    return User::ROLE_ENTREPRISE_CHEF;
                case 'bureau_controle':
                    return User::ROLE_BUREAU_CONTROLE_CHEF;
                case 'bureau_etudes':
                    return User::ROLE_BUREAU_ETUDES_CHEF;
                case 'maitre_ouvrage':
                    return User::ROLE_MAITRE_OUVRAGE_CHEF;
                default:
                    return User::ROLE_CONTROLEUR;
            }
        }

        // Collaborateur
        switch ($structure->type) {
            case 'entreprise':
                return User::ROLE_ENTREPRISE_COLLABORATEUR;
            case 'bureau_controle':
                return User::ROLE_BUREAU_CONTROLE_COLLABORATEUR;
            case 'bureau_etudes':
                return User::ROLE_BUREAU_ETUDES_COLLABORATEUR;
            case 'maitre_ouvrage':
                return User::ROLE_MAITRE_OUVRAGE_COLLABORATEUR;
            default:
                return User::ROLE_CONTROLEUR;
        }
    }

    // ============================================================
    // GESTION DES LOTS
    // ============================================================

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

        $lot->update([
            'nom' => $request->nom,
            'code' => $request->code,
            'description' => $request->description,
            'actif' => $request->actif ?? true,
        ]);

        return redirect()->route('admin.lots')->with('success', 'Lot modifié avec succès.');
    }

    public function destroyLot(Lot $lot)
    {
        if ($lot->users()->count() > 0) {
            return redirect()->route('admin.lots')->with('error', 'Impossible de supprimer ce lot car des utilisateurs y sont affectés.');
        }

        $lot->delete();
        return redirect()->route('admin.lots')->with('success', 'Lot supprimé avec succès.');
    }
}
