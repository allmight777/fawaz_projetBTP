<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentAssignmentRequest;
use App\Models\AuditLog;
use App\Models\DocumentAssignment;
use App\Models\DocumentVersion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Models\Dossier;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\CircuitVisaService;
use App\Notifications\ControleurAffecte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentAssignmentController extends Controller
{
    /** Collaborateurs actifs de la structure du responsable connecté (BC, BE ou MOE). */
    private function collaborateursDe(User $chef)
    {
        return User::collaborateursVisa($chef->structureVisa() ?? 'bureau_controle')
            ->where('actif', true)
            ->when($chef->structure_id, fn ($q) => $q->where('structure_id', $chef->structure_id))
            ->get();
    }

    // Formulaire d'affectation d'un ou plusieurs collaborateurs (responsable de la structure)
    public function create(DocumentVersion $documentVersion)
    {
        abort_if($documentVersion->dossier->isArchived(), 403, 'Ce document est archivé et ne peut plus être modifié.');
        abort_unless(
            app(CircuitVisaService::class)->peutAffecter(Auth::user(), $documentVersion),
            403,
            'Cette version ne peut plus être affectée par votre structure (déjà en analyse, visée ou à corriger).'
        );

        $documentVersion->load('dossier.documentType');
        $controleurs = $this->collaborateursDe(Auth::user());

        return view('dossiers.affecter', compact('documentVersion', 'controleurs'));
    }

    public function store(StoreDocumentAssignmentRequest $request, DocumentVersion $documentVersion)
    {
        abort_if($documentVersion->dossier->isArchived(), 403, 'Ce document est archivé et ne peut plus être modifié.');
        $circuit = app(CircuitVisaService::class);
        abort_unless(
            $circuit->peutAffecter(Auth::user(), $documentVersion),
            403,
            'Cette version ne peut plus être affectée par votre structure (déjà en analyse, visée ou à corriger).'
        );

        // On n'affecte qu'aux collaborateurs de sa propre structure.
        $autorises = $this->collaborateursDe(Auth::user())->pluck('id')->all();
        abort_if(array_diff($request->controleurs, $autorises), 403, 'Vous ne pouvez affecter que des collaborateurs de votre structure.');

        foreach ($request->controleurs as $controleurId) {
            $affectation = DocumentAssignment::create([
                'document_version_id' => $documentVersion->id,
                'controleur_id' => $controleurId,
                'specialite' => $request->specialite,
                'affecte_par' => Auth::id(),
                'date_affectation' => now(),
                'date_limite' => $request->date_limite,
                'statut' => 'en_attente',
            ]);

            $affectation->controleur->notify(new ControleurAffecte($affectation));
        }

        $circuit->demarrerAnalyse($documentVersion, Auth::user());

        AuditLog::enregistrer('AFFECTATION', $documentVersion->dossier_id, [
            'document_version_id' => $documentVersion->id,
            'controleurs' => $request->controleurs,
        ]);

        return redirect()->route('dossiers.show', $documentVersion->dossier_id)
            ->with('success', 'Collaborateur(s) affecté(s) avec succès.');
    }

    // Liste des affectations du contrôleur connecté
    public function mesAffectations()
    {
        $affectations = DocumentAssignment::with('documentVersion.dossier.documentType')
            ->where('controleur_id', Auth::id())
            ->latest()
            ->paginate(200);

        return view('dossiers.mes-affectations', compact('affectations'));
    }

    // Écran d'analyse d'un contrôleur pour une affectation donnée
    public function show(DocumentAssignment $documentAssignment)
    {
        if ($documentAssignment->controleur_id !== Auth::id()) {
            abort(403);
        }

        $documentAssignment->load([
            'documentVersion.dossier.documentType.checklistItems',
            'documentVersion.checklistReponses',
            'observations.auteur',
        ]);

        return view('dossiers.analyse', compact('documentAssignment'));
    }

 public function enregistrerReponses(Request $request, DocumentAssignment $documentAssignment)
{
    if ($documentAssignment->controleur_id !== Auth::id()) {
        abort(403);
    }
    abort_if($documentAssignment->documentVersion->dossier->isArchived(), 403, 'Ce document est archivé et ne peut plus être modifié.');

    $request->validate([
        'reponses' => 'nullable|array',
        'reponses.*' => 'boolean',
    ]);

    $version = $documentAssignment->documentVersion;

    foreach ($request->input('reponses', []) as $itemId => $valeur) {
        \App\Models\ChecklistReponse::updateOrCreate(
            ['document_version_id' => $version->id, 'checklist_item_id' => $itemId],
            ['valeur' => (bool) $valeur, 'repondu_par' => Auth::id()]
        );
    }

    return back()->with('success', 'Vérifications enregistrées.');
}

/**
 * Le contrôleur affecté (collaborateur) termine son analyse : il ne décide
 * plus lui-même (valider/rejeter appartient au responsable via
 * DocumentDecisionController), il indique seulement que ses observations
 * sont prêtes pour la consolidation du chef.
 */
public function marquerTermine(Request $request, DocumentAssignment $documentAssignment)
{
    if ($documentAssignment->controleur_id !== Auth::id()) {
        abort(403);
    }
    abort_if($documentAssignment->documentVersion->dossier->isArchived(), 403, 'Ce document est archivé et ne peut plus être modifié.');
    abort_if($documentAssignment->statut === 'termine', 403, 'Votre analyse est déjà terminée.');

    // Une seule action : vérifications + observation (facultative) + fin d'analyse.
    $request->validate([
        'reponses' => 'nullable|array',
        'reponses.*' => 'boolean',
        'type' => 'nullable|in:observation,non_conformite',
        'contenu' => 'nullable|string|max:5000',
    ], [
        'contenu.max' => 'L\'observation est trop longue (5000 caractères maximum).',
    ]);

    $version = $documentAssignment->documentVersion;
    $contenu = trim((string) $request->input('contenu'));

    DB::transaction(function () use ($request, $documentAssignment, $version, $contenu) {
        // Toutes les vérifications de la checklist : cochées = oui, non cochées = non
        // (une case décochée remplace bien une réponse précédente).
        foreach ($version->dossier->documentType->checklistItems ?? [] as $item) {
            \App\Models\ChecklistReponse::updateOrCreate(
                ['document_version_id' => $version->id, 'checklist_item_id' => $item->id],
                ['valeur' => (bool) $request->input('reponses.' . $item->id, false), 'repondu_par' => Auth::id()]
            );
        }

        if ($contenu !== '') {
            \App\Models\DocumentObservation::create([
                'document_assignment_id' => $documentAssignment->id,
                'auteur_id' => Auth::id(),
                'type' => $request->input('type', 'observation'),
                'contenu' => $contenu,
            ]);
            AuditLog::enregistrer('OBSERVATION', $version->dossier_id, ['document_assignment_id' => $documentAssignment->id]);
        }

        $documentAssignment->update(['statut' => 'termine']);

        AuditLog::enregistrer('ANALYSE_TERMINEE', $version->dossier_id, [
            'document_assignment_id' => $documentAssignment->id,
        ]);
    });

    return redirect()->route(\App\Support\Espaces::nomRoute('controleur.affectations.index'))->with('success', 'Analyse terminée : vérifications et observation enregistrées. Le responsable peut maintenant consolider et décider.');
}

/**
 * Affiche un aperçu du document pour le contrôleur
 */
public function apercuDocument($documentAssignmentId, $dossierId, $versionId)
{
    $user = Auth::user();

    $documentAssignment = DocumentAssignment::where('id', $documentAssignmentId)
        ->where('controleur_id', $user->id)
        ->firstOrFail();

    $dossier = Dossier::findOrFail($dossierId);
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
}
