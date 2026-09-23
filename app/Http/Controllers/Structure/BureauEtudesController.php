<?php

namespace App\Http\Controllers\Structure;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Lot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BureauEtudesController extends Controller
{
    /**
     * Dashboard pour le chef du bureau d'études
     */
    public function chefDashboard()
    {
        $user = Auth::user();

        // ===== STATISTIQUES GÉNÉRALES =====
        $totalDemandes = Demande::count();
        $enAttente = Demande::where('statut', 'EN ATTENTE')->count();
        $enControle = Demande::where('statut', 'EN CONTROLE')->count();
        $validees = Demande::where('statut', 'VALIDE')->count();
        $rejetees = Demande::where('statut', 'REJETE')->count();

        // ===== DEMANDES RÉCENTES =====
        $demandesRecentes = Demande::with(['soumisPar', 'lot'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // ===== DONNÉES POUR L'ÉVOLUTION (6 DERNIERS MOIS) =====
        $months = [];
        $evolutionData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M');
            $evolutionData[] = Demande::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        }

        // ===== RÉPARTITION PAR TYPE DE DOCUMENT =====
        $typeStats = Demande::select('type_document', DB::raw('count(*) as total'))
            ->groupBy('type_document')
            ->pluck('total', 'type_document')
            ->toArray();

        // Définir tous les types possibles avec 0 par défaut
        $allTypes = ['Plan', 'Rapport', 'Contrat', 'Devis', 'Facture', 'Autre'];
        $typeData = [];
        foreach ($allTypes as $type) {
            $typeData[$type] = $typeStats[$type] ?? 0;
        }

        $typeLabels = array_keys($typeData);
        $typeValues = array_values($typeData);

        // ===== STATISTIQUES PAR LOT =====
        $statsParLot = Demande::select('lot_id', DB::raw('count(*) as total'))
            ->whereNotNull('lot_id')
            ->groupBy('lot_id')
            ->with('lot')
            ->get()
            ->map(function ($item) {
                return [
                    'lot' => $item->lot->numero_lot ?? $item->lot->name ?? 'Sans lot',
                    'total' => $item->total
                ];
            });

        // ===== DEMANDES EN RETARD =====
        $demandesEnRetard = Demande::where('statut_delai', 'EN RETARD')
            ->with(['soumisPar', 'lot'])
            ->orderBy('echeance_controle', 'asc')
            ->limit(5)
            ->get();

        return view('bureau_etudes.chef.dashboard', compact(
            'user',
            'totalDemandes',
            'enAttente',
            'enControle',
            'validees',
            'rejetees',
            'demandesRecentes',
            'months',
            'evolutionData',
            'typeLabels',
            'typeValues',
            'statsParLot',
            'demandesEnRetard'
        ));
    }

    /**
     * Dashboard pour le collaborateur (ingénieur d'études)
     */
    public function collaborateurDashboard()
    {
        $user = Auth::user();

        // Statistiques pour le collaborateur connecté
        $mesDemandes = Demande::where('soumis_par', $user->id)->count();
        $mesDemandesEnAttente = Demande::where('soumis_par', $user->id)
            ->where('statut', 'EN ATTENTE')
            ->count();
        $mesDemandesValidees = Demande::where('soumis_par', $user->id)
            ->where('statut', 'VALIDE')
            ->count();
        $mesDemandesRejetees = Demande::where('soumis_par', $user->id)
            ->where('statut', 'REJETE')
            ->count();

        // Mes dernières demandes
        $mesDernieresDemandes = Demande::where('soumis_par', $user->id)
            ->with(['lot', 'controleur'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('bureau_etudes.collaborateur.dashboard', compact(
            'user',
            'mesDemandes',
            'mesDemandesEnAttente',
            'mesDemandesValidees',
            'mesDemandesRejetees',
            'mesDernieresDemandes'
        ));
    }

    /**
     * Liste des projets d'études
     */
    public function projets()
    {
        $user = Auth::user();
        $projets = Lot::withCount('demandes')->get();
        return view('bureau_etudes.projets', compact('user', 'projets'));
    }

    /**
     * Liste des plans et dessins
     */
    public function plans()
    {
        $user = Auth::user();
        $plans = Demande::where('type_document', 'Plan')
            ->with(['soumisPar', 'lot'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('bureau_etudes.plans', compact('user', 'plans'));
    }

    /**
     * Liste des notes de calcul
     */
    public function notesCalcul()
    {
        $user = Auth::user();
        $notes = Demande::where('type_document', 'Rapport')
            ->with(['soumisPar', 'lot'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('bureau_etudes.notes_calcul', compact('user', 'notes'));
    }

    /**
     * Détails d'une demande pour le chef
     */
    public function chefDemandeShow($id)
    {
        $user = Auth::user();
        $demande = Demande::with(['soumisPar', 'lot', 'controleur', 'documents'])
            ->findOrFail($id);
        return view('bureau_etudes.chef.demande_show', compact('user', 'demande'));
    }

    /**
     * Liste des contrôleurs
     */
    public function controleurs()
    {
        $user = Auth::user();
        $controleurs = User::where('role', 'controleur')
            ->orWhere('role', 'Contrôleur')
            ->withCount(['demandesControlees' => function ($query) {
                $query->where('statut', 'EN CONTROLE');
            }])
            ->get();
        return view('bureau_etudes.chef.controleurs', compact('user', 'controleurs'));
    }

    /**
     * Statistiques détaillées
     */
    public function statistiques()
    {
        $user = Auth::user();

        // Statistiques par statut
        $statsStatut = Demande::select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        // Statistiques par type de document
        $statsType = Demande::select('type_document', DB::raw('count(*) as total'))
            ->groupBy('type_document')
            ->pluck('total', 'type_document')
            ->toArray();

        // Statistiques par mois (année en cours)
        $statsMois = Demande::select(
                DB::raw('MONTH(created_at) as mois'),
                DB::raw('YEAR(created_at) as annee'),
                DB::raw('count(*) as total')
            )
            ->whereYear('created_at', now()->year)
            ->groupBy('mois', 'annee')
            ->orderBy('mois')
            ->get();

        return view('bureau_etudes.chef.statistiques', compact(
            'user',
            'statsStatut',
            'statsType',
            'statsMois'
        ));
    }

    /**
     * Historique des demandes
     */
    public function historique()
    {
        $user = Auth::user();
        $historique = Demande::with(['soumisPar', 'lot', 'controleur'])
            ->orderBy('updated_at', 'desc')
            ->paginate(30);
        return view('bureau_etudes.chef.historique', compact('user', 'historique'));
    }

    /**
     * Archives
     */
    public function archives()
    {
        $user = Auth::user();
        $archives = Demande::where('statut', 'ARCHIVE')
            ->with(['soumisPar', 'lot'])
            ->orderBy('updated_at', 'desc')
            ->paginate(30);
        return view('bureau_etudes.chef.archives', compact('user', 'archives'));
    }

    /**
     * Liste des demandes
     */
    public function chefDemandes(Request $request)
    {
        $user = Auth::user();

        $query = Demande::with(['soumisPar', 'lot', 'controleur']);

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('type_document')) {
            $query->where('type_document', $request->type_document);
        }
        if ($request->filled('lot_id')) {
            $query->where('lot_id', $request->lot_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_demande', 'LIKE', "%{$search}%")
                  ->orWhere('titre_document', 'LIKE', "%{$search}%")
                  ->orWhere('entreprise', 'LIKE', "%{$search}%");
            });
        }

        $demandes = $query->orderBy('created_at', 'desc')->paginate(20);
        $lots = Lot::all();
        $statuts = ['EN ATTENTE', 'EN CONTROLE', 'VALIDE', 'REJETE', 'ARCHIVE'];
        $types = ['Plan', 'Rapport', 'Contrat', 'Devis', 'Facture', 'Autre'];

        return view('bureau_etudes.chef.demandes', compact(
            'user',
            'demandes',
            'lots',
            'statuts',
            'types'
        ));
    }
}
