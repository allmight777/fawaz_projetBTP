<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Demande;
use App\Models\Lot;
use App\Models\Document;
use App\Models\Dossier;
use App\Models\HistoriqueDemande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ChefLotController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Base : tous les dossiers qui ont été envoyés à ce chef
        $baseQuery = fn() => Dossier::transmisA($user);

        $totalDocuments = $baseQuery()->count();
        $documentsEnAttente = $baseQuery()->whereIn('statut', ['soumis', 'en_cours'])->count();
        $documentsValides = $baseQuery()->where('statut', 'valide')->count();
        $documentsACorriger = $baseQuery()->where('statut', 'a_corriger')->count();
        $documentsEnAnalyse = $baseQuery()->where('statut', 'en_analyse')->count();

        $totalCollaborateurs = User::where('structure_id', $user->structure_id)
            ->where('categorie_role', 'collaborateur')
            ->count();
        $collaborateursActifs = User::where('structure_id', $user->structure_id)
            ->where('categorie_role', 'collaborateur')
            ->where('actif', true)
            ->count();

        $documentsRecents = $baseQuery()
            ->with(['documentType', 'creePar.structure'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $structureLabels = ['entreprise' => 'Entreprise', 'bureau_etudes' => "Bureau d'Études", 'maitre_ouvrage' => "Maître d'Ouvrage"];
        $repartitionStructures = [];
        foreach ($structureLabels as $type => $label) {
            $repartitionStructures[$label] = $baseQuery()
                ->whereHas('creePar.structure', fn($q) => $q->where('type', $type))
                ->count();
        }

        $months = [];
        $documentsData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M Y');
            $documentsData[] = $baseQuery()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return view('cheflot.dashboard', compact(
            'totalDocuments',
            'documentsEnAttente',
            'documentsValides',
            'documentsACorriger',
            'documentsEnAnalyse',
            'totalCollaborateurs',
            'collaborateursActifs',
            'documentsRecents',
            'repartitionStructures',
            'months',
            'documentsData'
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
        $user = Auth::user();

        $controleurs = User::where('structure_id', $user->structure_id)
            ->where('categorie_role', 'collaborateur')
            ->get();

        foreach ($controleurs as $controleur) {
            $assignments = \App\Models\DocumentAssignment::where('controleur_id', $controleur->id);

            $controleur->stats = [
                'total' => (clone $assignments)->count(),
                'en_attente' => (clone $assignments)->where('statut', 'en_attente')->count(),
                'en_cours' => (clone $assignments)->where('statut', 'en_cours')->count(),
                'termine' => (clone $assignments)->where('statut', 'termine')->count(),
            ];
        }

        $totalChefs = User::where('structure_id', $user->structure_id)
            ->where('categorie_role', 'responsable_organisme')
            ->count();

        $topCollaborateur = $controleurs->sortByDesc(fn($c) => $c->stats['termine'])->first();

        $chartLabels = $controleurs->pluck('full_name')->values()->toArray();
        $chartDataTermine = $controleurs->pluck('stats.termine')->values()->toArray();
        $chartDataEnCours = $controleurs->pluck('stats.en_cours')->values()->toArray();

        $repartitionGlobale = [
            'En attente' => $controleurs->sum(fn($c) => $c->stats['en_attente']),
            'En cours' => $controleurs->sum(fn($c) => $c->stats['en_cours']),
            'Terminés' => $controleurs->sum(fn($c) => $c->stats['termine']),
        ];

        return view('cheflot.controleurs', compact(
            'controleurs', 'totalChefs', 'topCollaborateur',
            'chartLabels', 'chartDataTermine', 'chartDataEnCours', 'repartitionGlobale'
        ));
    }

    public function statistiques(Request $request)
    {
        $user = Auth::user();

        $dateDebut = $request->filled('date_debut') ? \Carbon\Carbon::parse($request->date_debut)->startOfDay() : now()->subMonths(11)->startOfMonth();
        $dateFin = $request->filled('date_fin') ? \Carbon\Carbon::parse($request->date_fin)->endOfDay() : now()->endOfDay();
        $collaborateurId = $request->collaborateur_id;

        $stats = $this->calculerStatistiques($user, $dateDebut, $dateFin, $collaborateurId);

        $collaborateurs = User::where('structure_id', $user->structure_id)
            ->where('categorie_role', 'collaborateur')
            ->get();

        return view('cheflot.demandes.statistiques', compact(
            'stats', 'collaborateurs', 'dateDebut', 'dateFin', 'collaborateurId'
        ));
    }

 public function exportStatistiquesPdf(Request $request)
{
    $user = Auth::user();

    $dateDebut = $request->filled('date_debut') ? \Carbon\Carbon::parse($request->date_debut)->startOfDay() : now()->subMonths(11)->startOfMonth();
    $dateFin = $request->filled('date_fin') ? \Carbon\Carbon::parse($request->date_fin)->endOfDay() : now()->endOfDay();
    $collaborateurId = $request->collaborateur_id;

    $stats = $this->calculerStatistiques($user, $dateDebut, $dateFin, $collaborateurId);

    $collaborateurSelectionne = $collaborateurId
        ? User::find($collaborateurId)
        : null;

    // ✅ Utilisation correcte de la façade PDF
    $pdf = Pdf::loadView('cheflot.demandes.statistiques-pdf', compact(
        'stats', 'dateDebut', 'dateFin', 'collaborateurSelectionne', 'user'
    ));

    $filename = 'rapport_controle_' . $dateDebut->format('Y-m-d') . '_' . $dateFin->format('Y-m-d') . '.pdf';

    return $pdf->download($filename);
}

    /**
     * Calcule toutes les statistiques pour une période et éventuellement un collaborateur donné.
     */
    private function calculerStatistiques($user, $dateDebut, $dateFin, $collaborateurId = null)
    {
        $baseQuery = fn() => Dossier::transmisA($user)
            ->whereBetween('dossiers.created_at', [$dateDebut, $dateFin]); // ✅ Spécifier la table

        // Si un collaborateur est sélectionné
        if ($collaborateurId) {
            $baseQuery = fn() => Dossier::transmisA($user)
                ->whereHas('versions.affectations', function ($q) use ($collaborateurId) {
                    $q->where('controleur_id', $collaborateurId);
                })
                ->whereBetween('dossiers.created_at', [$dateDebut, $dateFin]); // ✅ Spécifier la table
        }

        $totalDocuments = $baseQuery()->count();

        $parStatut = [
            'soumis' => $baseQuery()->where('statut', 'soumis')->count(),
            'en_cours' => $baseQuery()->where('statut', 'en_cours')->count(),
            'en_analyse' => $baseQuery()->where('statut', 'en_analyse')->count(),
            'valide' => $baseQuery()->where('statut', 'valide')->count(),
            'a_corriger' => $baseQuery()->where('statut', 'a_corriger')->count(),
        ];

        // ✅ CORRECTION : Spécifier la table pour created_at
        $parType = $baseQuery()
            ->join('document_types', 'dossiers.document_type_id', '=', 'document_types.id')
            ->selectRaw('document_types.nom as type_nom, count(*) as total')
            ->groupBy('document_types.nom')
            ->pluck('total', 'type_nom')
            ->toArray();

        $parStructure = [];
        $structureLabels = ['entreprise' => 'Entreprise', 'bureau_etudes' => "Bureau d'Études", 'maitre_ouvrage' => "Maître d'Ouvrage"];
        foreach ($structureLabels as $type => $label) {
            $parStructure[$label] = $baseQuery()
                ->whereHas('creePar.structure', fn($q) => $q->where('type', $type))
                ->count();
        }

        // Stats par collaborateur
        $collaborateursTous = User::where('structure_id', $user->structure_id)
            ->where('categorie_role', 'collaborateur')
            ->get();

        $parCollaborateur = [];
        foreach ($collaborateursTous as $c) {
            $count = \App\Models\DocumentAssignment::where('controleur_id', $c->id)
                ->where('statut', 'termine')
                ->whereBetween('created_at', [$dateDebut, $dateFin])
                ->count();
            if ($count > 0) {
                $parCollaborateur[$c->full_name] = $count;
            }
        }
        arsort($parCollaborateur);

        // Évolution mensuelle
        $evolutionMensuelle = [];
        $curseur = $dateDebut->copy()->startOfMonth();
        while ($curseur <= $dateFin) {
            $evolutionMensuelle[] = [
                'mois' => $curseur->format('M Y'),
                'total' => Dossier::transmisA($user)
                    ->whereYear('dossiers.created_at', $curseur->year)  // ✅ Spécifier la table
                    ->whereMonth('dossiers.created_at', $curseur->month) // ✅ Spécifier la table
                    ->count(),
            ];
            $curseur->addMonth();
        }

        return [
            'total_documents' => $totalDocuments,
            'par_statut' => $parStatut,
            'par_type' => $parType,
            'par_structure' => $parStructure,
            'par_collaborateur' => $parCollaborateur,
            'evolution_mensuelle' => $evolutionMensuelle,
        ];
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

    public function documentsRecus()
    {
        $user = Auth::user();

        $dossiers = Dossier::transmisA($user)
            ->with([
                'documentType',
                'versions' => function ($q) {
                    $q->orderByDesc('numero_version');
                },
                'creePar.structure',
            ])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('cheflot.documents-recus', compact('dossiers'));
    }

    public function voirDocumentRecu($id)
    {
        $user = Auth::user();

        $dossier = Dossier::transmisA($user)
            ->with(['documentType', 'versions.importePar', 'creePar.structure', 'transmissions.emetteur'])
            ->findOrFail($id);

        return view('cheflot.document-recu-detail', compact('dossier'));
    }

    public function telechargerDocumentRecu($dossierId, $versionId)
    {
        $user = Auth::user();

        $dossier = Dossier::transmisA($user)->findOrFail($dossierId);

        $version = $dossier->versions()->findOrFail($versionId);

        if (!$version->chemin_a_servir || !Storage::disk('public')->exists($version->chemin_a_servir)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        return Storage::disk('public')->download($version->chemin_a_servir, $version->nom_affiche);
    }

    public function traiterDecision(Request $request, $id)
    {
        $user = Auth::user();

        $dossier = Dossier::transmisA($user)->findOrFail($id);

        $request->validate([
            'decision' => 'required|in:valide,a_corriger',
            'commentaires' => 'nullable|string|max:2000',
        ], [
            'decision.required' => 'Veuillez choisir une décision.',
        ]);

        $derniereVersion = $dossier->versions()->orderByDesc('numero_version')->first();

        if (!$derniereVersion) {
            return back()->with('error', 'Aucune version trouvée pour ce dossier.');
        }

        DB::transaction(function () use ($request, $user, $dossier, $derniereVersion) {
            \App\Models\DocumentDecision::create([
                'document_version_id' => $derniereVersion->id,
                'decision' => $request->decision,
                'validateur_id' => $user->id,
                'date_decision' => now(),
                'commentaires' => $request->commentaires,
            ]);

            $derniereVersion->update([
                'statut' => $request->decision === 'valide' ? 'valide' : 'a_corriger',
            ]);

            $dossier->update([
                'statut' => $request->decision === 'valide' ? 'valide' : 'a_corriger',
            ]);

            if ($request->decision === 'valide') {
                app(\App\Services\DocumentStampingService::class)->tamponner($derniereVersion);
            }
        });

        $message = $request->decision === 'valide'
            ? 'Document validé avec succès.'
            : 'Document renvoyé pour correction.';

        return redirect()->route('cheflot.documents.recus')->with('success', $message);
    }

    public function apercuDocumentRecu($dossierId, $versionId)
    {
        $user = Auth::user();

        $dossier = Dossier::transmisA($user)->findOrFail($dossierId);

        $version = $dossier->versions()->findOrFail($versionId);

        if (!$version->chemin_a_servir || !Storage::disk('public')->exists($version->chemin_a_servir)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        $path = Storage::disk('public')->path($version->chemin_a_servir);
        $mimeType = Storage::disk('public')->mimeType($version->chemin_a_servir);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $version->nom_affiche . '"',
        ]);
    }

    public function assignerForm($id)
    {
        $user = Auth::user();

        $dossier = Dossier::transmisA($user)->findOrFail($id);

        $collaborateurs = User::where('structure_id', $user->structure_id)
            ->where('categorie_role', 'collaborateur')
            ->get();

        return response()->json([
            'collaborateurs' => $collaborateurs->map(fn($c) => [
                'id' => $c->id,
                'full_name' => $c->full_name,
                'specialite' => $c->specialite,
            ]),
        ]);
    }

    public function assignerDocument(Request $request, $id)
    {
        $user = Auth::user();

        $dossier = Dossier::transmisA($user)->findOrFail($id);

        $request->validate([
            'controleur_id' => 'required|exists:users,id',
            'specialite' => 'nullable|string|max:255',
            'date_limite' => 'nullable|date|after:now',
        ], [
            'controleur_id.required' => 'Veuillez choisir un collaborateur.',
            'date_limite.after' => 'Le délai doit être fixé à une date/heure future.',
        ]);

        $collaborateur = User::where('id', $request->controleur_id)
            ->where('structure_id', $user->structure_id)
            ->firstOrFail();

        $derniereVersion = $dossier->versions()->orderByDesc('numero_version')->first();

        if (!$derniereVersion) {
            return back()->with('error', 'Aucune version trouvée pour ce dossier.');
        }

        DB::transaction(function () use ($request, $user, $dossier, $derniereVersion, $collaborateur) {
            $affectation = \App\Models\DocumentAssignment::create([
                'document_version_id' => $derniereVersion->id,
                'controleur_id' => $collaborateur->id,
                'specialite' => $request->specialite,
                'affecte_par' => $user->id,
                'date_affectation' => now(),
                'date_limite' => $request->date_limite,
                'statut' => 'en_attente',
            ]);

            $dossier->update(['statut' => 'en_analyse']);

            try {
                Mail::to($collaborateur->email)->send(new \App\Mail\DocumentAssignedMail($derniereVersion, $dossier, $user, $affectation));
                Log::info('Email d\'assignation envoyé à ' . $collaborateur->email);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email assignation: ' . $e->getMessage());
            }
        });

        return redirect()->route('cheflot.documents.recus.show', $dossier->id)
            ->with('success', "Document assigné à {$collaborateur->full_name}.");
    }
}
