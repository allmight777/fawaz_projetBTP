<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Demande;
use App\Models\Lot;
use App\Models\Document;
use App\Models\HistoriqueDemande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChefLotController extends Controller
{
    public function dashboard()
    {
        $totalControleurs = User::where('role', 'CONTROLEUR')->count();
        $controleursActifs = User::where('role', 'CONTROLEUR')->where('actif', true)->count();
        $demandesEnAttente = Demande::where('statut', 'EN ATTENTE')->count();
        $demandesEnControle = Demande::where('statut', 'EN CONTROLE')->count();
        $demandesValidees = Demande::where('statut', 'VALIDE')->count();
        $demandesRejetees = Demande::where('statut', 'REJETE')->count();
        $totalDemandes = Demande::count();
        $demandesRecentes = Demande::with(['soumisPar', 'lot', 'controleur'])->latest()->take(5)->get();
        $demandesEnRetard = Demande::where('statut_delai', 'EN RETARD')->where('statut', 'EN CONTROLE')->count();

        $priorites = [
            'Basse' => Demande::where('priorite', 'Basse')->count(),
            'Moyenne' => Demande::where('priorite', 'Moyenne')->count(),
            'Haute' => Demande::where('priorite', 'Haute')->count(),
            'Urgente' => Demande::where('priorite', 'Urgente')->count(),
        ];

        $months = [];
        $demandesData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M Y');
            $demandesData[] = Demande::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count();
        }

        return view('cheflot.dashboard', compact(
            'totalControleurs', 'controleursActifs', 'demandesEnAttente', 'demandesEnControle',
            'demandesValidees', 'demandesRejetees', 'totalDemandes', 'demandesRecentes',
            'demandesEnRetard', 'priorites', 'months', 'demandesData'
        ));
    }

    public function demandes()
    {
        $demandes = Demande::with(['soumisPar', 'lot', 'controleur', 'document'])->latest()->paginate(15);
        $demandesEnAttente = Demande::where('statut', 'EN ATTENTE')->count();
        $demandesEnControle = Demande::where('statut', 'EN CONTROLE')->count();
        $demandesValidees = Demande::where('statut', 'VALIDE')->count();
        $demandesRejetees = Demande::where('statut', 'REJETE')->count();
        $totalDemandes = Demande::count();

        $priorites = [
            'Basse' => Demande::where('priorite', 'Basse')->count(),
            'Moyenne' => Demande::where('priorite', 'Moyenne')->count(),
            'Haute' => Demande::where('priorite', 'Haute')->count(),
            'Urgente' => Demande::where('priorite', 'Urgente')->count(),
        ];

        $months = [];
        $demandesData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M Y');
            $demandesData[] = Demande::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count();
        }

        return view('cheflot.demandes.demandes', compact(
            'demandes', 'demandesEnAttente', 'demandesEnControle', 'demandesValidees',
            'demandesRejetees', 'totalDemandes', 'priorites', 'months', 'demandesData'
        ));
    }

    public function demandesEnAttente()
    {
        $demandes = Demande::with(['soumisPar', 'lot'])->where('statut', 'EN ATTENTE')->latest()->paginate(15);
        return view('cheflot.demandes.demandes-attente', compact('demandes'));
    }

    public function demandesEnControle()
    {
        $demandes = Demande::with(['soumisPar', 'lot', 'controleur'])->where('statut', 'EN CONTROLE')->latest()->paginate(15);
        return view('cheflot.demandes.demandes-controle', compact('demandes'));
    }

    public function demandesValidees()
    {
        $demandes = Demande::with(['soumisPar', 'lot'])->where('statut', 'VALIDE')->latest()->paginate(15);
        return view('cheflot.demandes.demandes-validees', compact('demandes'));
    }

    public function demandesRejetees()
    {
        $demandes = Demande::with(['soumisPar', 'lot'])->where('statut', 'REJETE')->latest()->paginate(15);
        return view('cheflot.demandes.demandes-rejetees', compact('demandes'));
    }

    public function assignerControleur(Request $request, Demande $demande)
    {
        $request->validate(['controleur_id' => 'required|exists:users,id']);

        $ancienStatut = $demande->statut;
        $controleur = User::find($request->controleur_id);

        $demande->controleur_id = $request->controleur_id;
        $demande->statut = 'EN CONTROLE';
        $demande->echeance_controle = now()->addDays(7);
        $demande->save();

        // CRÉATION DE L'HISTORIQUE D'ASSIGNATION
        HistoriqueDemande::create([
            'demande_id' => $demande->id,
            'user_id' => Auth::id(),
            'action' => 'ASSIGNATION',
            'details' => 'Assigné à ' . ($controleur->full_name ?? 'contrôleur'),
            'ancien_statut' => $ancienStatut,
            'nouveau_statut' => 'EN CONTROLE',
            'ancienne_version' => $demande->version,
            'nouvelle_version' => $demande->version,
        ]);

        return redirect()->back()->with('success', 'Contrôleur assigné avec succès.');
    }

    public function voirDemande($id)
    {
        $demande = Demande::with(['soumisPar', 'lot', 'controleur', 'document', 'versions', 'historiques.user'])->findOrFail($id);
        $controleurs = User::where('role', 'CONTROLEUR')->where('actif', true)->get();
        return view('cheflot.demandes.demande-detail', compact('demande', 'controleurs'));
    }

    public function historiqueDemande($id)
    {
        $demande = Demande::with(['historiques.user', 'versions'])->findOrFail($id);
        return view('cheflot.demandes.demande-historique', compact('demande'));
    }

    public function controleurs()
    {
        $controleurs = User::where('role', 'CONTROLEUR')->with(['lot'])->get();
        foreach ($controleurs as $controleur) {
            $controleur->stats = [
                'total' => Demande::where('controleur_id', $controleur->id)->count(),
                'en_attente' => Demande::where('controleur_id', $controleur->id)->where('statut', 'EN ATTENTE')->count(),
                'en_controle' => Demande::where('controleur_id', $controleur->id)->where('statut', 'EN CONTROLE')->count(),
                'validees' => Demande::where('controleur_id', $controleur->id)->where('statut', 'VALIDE')->count(),
                'rejetees' => Demande::where('controleur_id', $controleur->id)->where('statut', 'REJETE')->count(),
            ];
        }
        return view('cheflot.controleurs', compact('controleurs'));
    }

public function statistiques()
{
    $stats = [
        'total_demandes' => Demande::count(),
        'par_statut' => [
            'EN ATTENTE' => Demande::where('statut', 'EN ATTENTE')->count(),
            'EN CONTROLE' => Demande::where('statut', 'EN CONTROLE')->count(),
            'VALIDE' => Demande::where('statut', 'VALIDE')->count(),
            'REJETE' => Demande::where('statut', 'REJETE')->count(),
        ],
        'par_priorite' => [
            'Basse' => Demande::where('priorite', 'Basse')->count(),
            'Moyenne' => Demande::where('priorite', 'Moyenne')->count(),
            'Haute' => Demande::where('priorite', 'Haute')->count(),
            'Urgente' => Demande::where('priorite', 'Urgente')->count(),
        ],
        'par_lot' => [],
        'evolution_mensuelle' => [],
    ];

    foreach (Lot::where('actif', true)->get() as $lot) {
        $stats['par_lot'][$lot->nom] = Demande::where('lot_id', $lot->id)->count();
    }

    for ($i = 11; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $stats['evolution_mensuelle'][] = [
            'mois' => $month->format('M Y'),
            'total' => Demande::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count(),
        ];
    }

    return view('cheflot.demandes.statistiques', compact('stats'));
}

    public function telechargerDocument(Document $document)
    {
        if ($document->chemin_fichier && Storage::exists($document->chemin_fichier)) {
            return Storage::download($document->chemin_fichier);
        }
        return redirect()->back()->with('error', 'Fichier non trouvé.');
    }

    public function historiqueGlobal(Request $request)
    {
        $query = HistoriqueDemande::with(['demande', 'user']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('statut')) {
            $query->whereHas('demande', function($q) use ($request) {
                $q->where('statut', $request->statut);
            });
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $historiques = $query->orderBy('created_at', 'desc')->paginate(20);
        $actions = HistoriqueDemande::distinct()->pluck('action');
        $statuts = ['EN ATTENTE', 'EN CONTROLE', 'VALIDE', 'REJETE', 'ARCHIVE'];
        $utilisateurs = User::where('role', 'CONTROLEUR')->orWhere('role', 'CHEF LOT')->get();

        $stats = [
            'total' => HistoriqueDemande::count(),
            'creations' => HistoriqueDemande::where('action', 'CREATION')->count(),
            'assignations' => HistoriqueDemande::where('action', 'ASSIGNATION')->count(),
            'validations' => HistoriqueDemande::where('action', 'VALIDATION')->count(),
            'rejets' => HistoriqueDemande::where('action', 'REJET')->count(),
        ];

        return view('cheflot.historique-global', compact('historiques', 'actions', 'statuts', 'utilisateurs', 'stats'));
    }

    public function exportHistorique(Request $request)
    {
        $query = HistoriqueDemande::with(['demande', 'user']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('statut')) {
            $query->whereHas('demande', function($q) use ($request) {
                $q->where('statut', $request->statut);
            });
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $historiques = $query->orderBy('created_at', 'desc')->get();
        $filename = 'historique_demandes_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://temp', 'w+');

        fputcsv($handle, ['ID', 'Date', 'Action', 'Utilisateur', 'Demande N°', 'Titre document', 'Ancien statut', 'Nouveau statut', 'Ancienne version', 'Nouvelle version', 'Détails'], ';');

        foreach ($historiques as $h) {
            fputcsv($handle, [
                $h->id, $h->created_at->format('d/m/Y H:i:s'), $h->action,
                $h->user->full_name ?? '-', $h->demande->numero_demande ?? '-',
                $h->demande->titre_document ?? '-', $h->ancien_statut ?? '-',
                $h->nouveau_statut ?? '-', $h->ancienne_version ?? '-',
                $h->nouvelle_version ?? '-', $h->details ?? '-',
            ], ';');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
