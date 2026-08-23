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
use App\Notifications\ControleurAffecte;
use Illuminate\Support\Facades\Auth;

class DocumentAssignmentController extends Controller
{
    // Formulaire d'affectation d'un ou plusieurs contrôleurs (responsable du Bureau de Contrôle)
    public function create(DocumentVersion $documentVersion)
    {
        abort_unless($documentVersion->statut === 'soumis', 403, 'Cette version ne peut plus être affectée (déjà en analyse, validée ou à corriger).');

        $documentVersion->load('dossier.documentType');
        // Exclut les comptes "CONTROLEUR" qui appartiennent en réalité à une autre structure
        // (ex. comptes de test Entreprise/MO) — seuls les vrais contrôleurs du Bureau de
        // Contrôle, ou les contrôleurs historiques sans structure renseignée, sont proposés.
        $controleurs = User::where('role', 'CONTROLEUR')
            ->where('actif', true)
            ->where(function ($query) {
                $query->whereNull('structure_id')
                    ->orWhereHas('structure', fn ($q) => $q->where('type', 'bureau_controle'));
            })
            ->get();

        return view('dossiers.affecter', compact('documentVersion', 'controleurs'));
    }

    public function store(StoreDocumentAssignmentRequest $request, DocumentVersion $documentVersion)
    {
        abort_unless($documentVersion->statut === 'soumis', 403, 'Cette version ne peut plus être affectée (déjà en analyse, validée ou à corriger).');

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

        $documentVersion->update(['statut' => 'en_analyse']);
        $documentVersion->dossier->update(['statut' => 'en_analyse']);

        AuditLog::enregistrer('AFFECTATION', $documentVersion->dossier_id, [
            'document_version_id' => $documentVersion->id,
            'controleurs' => $request->controleurs,
        ]);

        return redirect()->route('dossiers.show', $documentVersion->dossier_id)
            ->with('success', 'Contrôleur(s) affecté(s) avec succès.');
    }

    // Liste des affectations du contrôleur connecté
    public function mesAffectations()
    {
        $affectations = DocumentAssignment::with('documentVersion.dossier.documentType')
            ->where('controleur_id', Auth::id())
            ->latest()
            ->paginate(15);

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
public function marquerTermine(DocumentAssignment $documentAssignment)
{
    if ($documentAssignment->controleur_id !== Auth::id()) {
        abort(403);
    }

    $documentAssignment->update(['statut' => 'termine']);

    AuditLog::enregistrer('ANALYSE_TERMINEE', $documentAssignment->documentVersion->dossier_id, [
        'document_assignment_id' => $documentAssignment->id,
    ]);

    return redirect()->route('controleur.affectations.index')->with('success', 'Analyse marquée comme terminée. Le responsable peut maintenant consolider et décider.');
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
