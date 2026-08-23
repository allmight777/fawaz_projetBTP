<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\ChefLotController;
use App\Http\Controllers\ControleurController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DocumentAssignmentController;
use App\Http\Controllers\DocumentObservationController;
use App\Http\Controllers\DocumentDecisionController;
use App\Http\Controllers\DocumentTransmissionController;
use App\Http\Controllers\ArchivageController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Structure\EntrepriseController;
use App\Http\Controllers\Structure\BureauControleController;
use App\Http\Controllers\Structure\BureauEtudesController;
use App\Http\Controllers\Structure\MaitreOuvrageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard par défaut
Route::get('/dashboard', function () {
    $user = auth()->user();

    if (!$user) {
        return redirect()->route('login');
    }

    return redirect()->route($user->getDashboardRoute());
})->middleware(['auth', 'verified'])->name('dashboard');

// ============================================================
// ROUTES POUR LES 4 STRUCTURES
// ============================================================

// === 1. ENTREPRISE ===
Route::middleware(['auth', 'verified'])->prefix('entreprise')->name('entreprise.')->group(function () {
    // Dashboards
    Route::get('/chef/dashboard', [EntrepriseController::class, 'chefDashboard'])->name('chef.dashboard');
    Route::get('/collaborateur/dashboard', [EntrepriseController::class, 'collaborateurDashboard'])->name('collaborateur.dashboard');
    // Route pour le téléchargement
Route::get('/dossiers/{id}/telecharger', [EntrepriseController::class, 'telechargerDossier'])->name('dossiers.telecharger');
Route::delete('/dossiers/{id}', [EntrepriseController::class, 'destroyDossier'])->name('dossiers.destroy');

    // Documents et dossiers
    Route::get('/documents', [EntrepriseController::class, 'documents'])->name('documents');
    Route::get('/dossiers', [EntrepriseController::class, 'dossiers'])->name('dossiers');
    Route::get('/dossiers/create', [EntrepriseController::class, 'createDossier'])->name('dossiers.create');
    Route::post('/dossiers', [EntrepriseController::class, 'storeDossier'])->name('dossiers.store');
    Route::get('/dossiers/{dossier}', [EntrepriseController::class, 'showDossier'])->name('dossiers.show');

    Route::get('/documents-a-transferer', [EntrepriseController::class, 'documentsATransferer'])->name('documents.a-transferer');
Route::post('/dossiers/{id}/transferer', [EntrepriseController::class, 'transfererDossier'])->name('dossiers.transferer');

    Route::get('/dossiers/{id}/corriger', [EntrepriseController::class, 'corrigerForm'])->name('dossiers.corriger');
Route::post('/dossiers/{id}/corriger', [EntrepriseController::class, 'storeCorrection'])->name('dossiers.corriger.store');

Route::get('/dossiers/{dossier}/versions/{version}/telecharger', [EntrepriseController::class, 'telechargerVersion'])->name('dossiers.versions.telecharger');

    // Transmissions
    Route::post('/transmettre', [EntrepriseController::class, 'transmettre'])->name('transmettre');

    // Checklists
    Route::get('/dossiers/{id}/checklist', [EntrepriseController::class, 'getChecklist'])->name('dossiers.checklist');
    Route::get('/document-types/{id}/checklist', [EntrepriseController::class, 'getChecklistByType'])->name('document-types.checklist');
});
// === 2. BUREAU DE CONTRÔLE ===
Route::middleware(['auth', 'verified'])->prefix('bureau-controle')->name('bureau_controle.')->group(function () {
    Route::get('/chef/dashboard', [BureauControleController::class, 'chefDashboard'])->name('chef.dashboard');
    Route::get('/controles', [BureauControleController::class, 'controles'])->name('controles');
    Route::get('/rapports', [BureauControleController::class, 'rapports'])->name('rapports');
    Route::get('/non-conformites', [BureauControleController::class, 'nonConformites'])->name('non_conformites');
    Route::get('/collaborateur/dashboard', [BureauControleController::class, 'collaborateurDashboard'])->name('collaborateur.dashboard');
});

// === 3. BUREAU D'ÉTUDES ===
Route::middleware(['auth', 'verified'])->prefix('bureau-etudes')->name('bureau_etudes.')->group(function () {
    Route::get('/chef/dashboard', [BureauEtudesController::class, 'chefDashboard'])->name('chef.dashboard');
    Route::get('/collaborateur/dashboard', [BureauEtudesController::class, 'collaborateurDashboard'])->name('collaborateur.dashboard');
    Route::get('/projets', [BureauEtudesController::class, 'projets'])->name('projets');
    Route::get('/plans', [BureauEtudesController::class, 'plans'])->name('plans');
    Route::get('/notes-calcul', [BureauEtudesController::class, 'notesCalcul'])->name('notes_calcul');
});

// === 4. MAÎTRE D'OUVRAGE ===
Route::middleware(['auth', 'verified'])->prefix('maitre-ouvrage')->name('maitre_ouvrage.')->group(function () {
    Route::get('/chef/dashboard', [MaitreOuvrageController::class, 'chefDashboard'])->name('chef.dashboard');
    Route::get('/collaborateur/dashboard', [MaitreOuvrageController::class, 'collaborateurDashboard'])->name('collaborateur.dashboard');
    Route::get('/archives', [MaitreOuvrageController::class, 'archives'])->name('archives');
    Route::get('/projets', [MaitreOuvrageController::class, 'projets'])->name('projets');
    Route::get('/chantiers', [MaitreOuvrageController::class, 'chantiers'])->name('chantiers');
    Route::get('/rapports', [MaitreOuvrageController::class, 'rapports'])->name('rapports');
});

// ============================================================
// ROUTES ADMIN
// ============================================================

Route::middleware(['auth', 'verified', 'role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::get('/lots', [AdminController::class, 'lots'])->name('lots');
    Route::get('/lots/create', [AdminController::class, 'createLot'])->name('lots.create');
    Route::post('/lots', [AdminController::class, 'storeLot'])->name('lots.store');
    Route::get('/lots/{lot}/edit', [AdminController::class, 'editLot'])->name('lots.edit');
    Route::put('/lots/{lot}', [AdminController::class, 'updateLot'])->name('lots.update');
    Route::delete('/lots/{lot}', [AdminController::class, 'destroyLot'])->name('lots.destroy');

    // Gestion des types de documents
    Route::prefix('document-types')->name('document-types.')->group(function () {
        Route::get('/', [DocumentTypeController::class, 'index'])->name('index');
        Route::get('/create', [DocumentTypeController::class, 'create'])->name('create');
        Route::post('/', [DocumentTypeController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [DocumentTypeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DocumentTypeController::class, 'update'])->name('update');
        Route::delete('/{id}', [DocumentTypeController::class, 'destroy'])->name('destroy');
        Route::get('/{documentTypeId}/checklist', [DocumentTypeController::class, 'checklistIndex'])->name('checklist');
        Route::post('/{documentTypeId}/checklist', [DocumentTypeController::class, 'checklistStore'])->name('checklist.store');
        Route::put('/{documentTypeId}/checklist/{itemId}', [DocumentTypeController::class, 'checklistUpdate'])->name('checklist.update');
        Route::delete('/{documentTypeId}/checklist/{itemId}', [DocumentTypeController::class, 'checklistDestroy'])->name('checklist.destroy');
        Route::post('/{documentTypeId}/checklist/reorder', [DocumentTypeController::class, 'checklistReorder'])->name('checklist.reorder');
    });
});

// ============================================================
// ROUTES DEMANDES
// ============================================================

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/demandes/create', [DemandeController::class, 'create'])->name('demandes.create');
    Route::post('/demandes', [DemandeController::class, 'store'])->name('demandes.store');
    Route::get('/demandes/{demande}/telecharger', [DemandeController::class, 'telechargerFichier'])->name('demandes.telecharger');
});

// ============================================================
// ROUTES CHEF LOT
// ============================================================

Route::middleware(['auth', 'verified'])->prefix('cheflot')->name('cheflot.')->group(function () {
    Route::get('/dashboard', [ChefLotController::class, 'dashboard'])->name('dashboard');

Route::get('/documents-recus', [ChefLotController::class, 'documentsRecus'])->name('documents.recus');
Route::get('/documents-recus/{id}', [ChefLotController::class, 'voirDocumentRecu'])->name('documents.recus.show');
Route::get('/documents-recus/{id}/collaborateurs', [ChefLotController::class, 'assignerForm'])->name('documents.recus.collaborateurs');
Route::post('/documents-recus/{id}/assigner', [ChefLotController::class, 'assignerDocument'])->name('documents.recus.assigner');
Route::get('/statistiques', [ChefLotController::class, 'statistiques'])->name('statistiques');
Route::get('/statistiques/export-pdf', [ChefLotController::class, 'exportStatistiquesPdf'])->name('statistiques.export-pdf');
Route::post('/documents-recus/{id}/decision', [ChefLotController::class, 'traiterDecision'])->name('documents.recus.decision');
Route::get('/documents-recus/{dossier}/versions/{version}/apercu', [ChefLotController::class, 'apercuDocumentRecu'])->name('documents.recus.apercu');
Route::get('/documents-recus/{dossier}/versions/{version}/telecharger', [ChefLotController::class, 'telechargerDocumentRecu'])->name('documents.recus.telecharger');

    Route::get('/demandes', [ChefLotController::class, 'demandes'])->name('demandes');
    Route::get('/demandes/attente', [ChefLotController::class, 'demandesEnAttente'])->name('demandes.attente');
    Route::get('/demandes/controle', [ChefLotController::class, 'demandesEnControle'])->name('demandes.controle');
    Route::get('/demandes/validees', [ChefLotController::class, 'demandesValidees'])->name('demandes.validees');
    Route::get('/demandes/rejetees', [ChefLotController::class, 'demandesRejetees'])->name('demandes.rejetees');
    Route::get('/demandes/{id}', [ChefLotController::class, 'voirDemande'])->name('demandes.show');
    Route::get('/demandes/{id}/historique', [ChefLotController::class, 'historiqueDemande'])->name('demandes.historique');
    Route::post('/demandes/{demande}/assigner', [ChefLotController::class, 'assignerControleur'])->name('demandes.assigner');
    Route::get('/controleurs', [ChefLotController::class, 'controleurs'])->name('controleurs');
    Route::get('/statistiques', [ChefLotController::class, 'statistiques'])->name('statistiques');
    Route::get('/documents/{document}/telecharger', [ChefLotController::class, 'telechargerDocument'])->name('document.telecharger');
    Route::get('/historique', [ChefLotController::class, 'historiqueGlobal'])->name('historique.global');
    Route::get('/historique/export', [ChefLotController::class, 'exportHistorique'])->name('historique.export');

    Route::get('/document-versions/{documentVersion}/affecter', [DocumentAssignmentController::class, 'create'])->name('dossiers.affecter');
    Route::post('/document-versions/{documentVersion}/affecter', [DocumentAssignmentController::class, 'store'])->name('dossiers.affecter.store');
    Route::get('/document-versions/{documentVersion}/decision', [DocumentDecisionController::class, 'create'])->name('dossiers.decision');
    Route::post('/document-versions/{documentVersion}/decision', [DocumentDecisionController::class, 'store'])->name('dossiers.decision.store');
    Route::post('/observations/{documentObservation}/retenue', [DocumentObservationController::class, 'toggleRetenue'])->name('observations.retenue');
    Route::get('/archives', [ArchivageController::class, 'index'])->name('archives.index');
    Route::get('/dossiers/{dossier}/diffusion', [DocumentTransmissionController::class, 'diffusionForm'])->name('dossiers.diffusion');
    Route::post('/dossiers/{dossier}/diffusion', [DocumentTransmissionController::class, 'diffuser'])->name('dossiers.diffusion.store');
});

// ============================================================
// ROUTES CONTROLEUR
// ============================================================

Route::middleware(['auth', 'verified'])->prefix('controleur')->name('controleur.')->group(function () {
    Route::get('/dashboard', [ControleurController::class, 'dashboard'])->name('dashboard');
    Route::get('/mon-lot', [ControleurController::class, 'monLot'])->name('mon-lot');
    Route::get('/taches', [ControleurController::class, 'taches'])->name('taches');
    // Route pour l'aperçu du document pour le contrôleur
Route::get('/affectations/{documentAssignment}/apercu/{dossier}/{version}', [DocumentAssignmentController::class, 'apercuDocument'])
    ->name('affectations.apercu');

    Route::post('/affectations/{documentAssignment}/reponses', [DocumentAssignmentController::class, 'enregistrerReponses'])->name('affectations.reponses');

    Route::get('/affectations', [DocumentAssignmentController::class, 'mesAffectations'])->name('affectations.index');
    Route::get('/affectations/{documentAssignment}', [DocumentAssignmentController::class, 'show'])->name('affectations.show');
 Route::post('/affectations/{documentAssignment}/terminer', [DocumentAssignmentController::class, 'marquerTermine'])->name('affectations.terminer');
    Route::post('/affectations/{documentAssignment}/observations', [DocumentObservationController::class, 'store'])->name('affectations.observations.store');
});

// ============================================================
// ROUTES GED
// ============================================================

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dossiers', [DossierController::class, 'index'])->name('dossiers.index');
    Route::get('/dossiers/create', [DossierController::class, 'create'])->name('dossiers.create');
    Route::post('/dossiers', [DossierController::class, 'store'])->name('dossiers.store');
    Route::get('/dossiers/{dossier}', [DossierController::class, 'show'])->name('dossiers.show');
    Route::get('/dossiers/{dossier}/telecharger/{version}', [DossierController::class, 'telecharger'])->name('dossiers.telecharger');
    Route::get('/dossiers/{dossier}/nouvelle-version', [DossierController::class, 'nouvelleVersionForm'])->name('dossiers.versions.create');
    Route::post('/dossiers/{dossier}/nouvelle-version', [DossierController::class, 'storeVersion'])->name('dossiers.versions.store');
    Route::get('/dossiers/{dossier}/transmettre', [DocumentTransmissionController::class, 'transmettreForm'])->name('dossiers.transmettre.form');
    Route::post('/dossiers/{dossier}/transmettre', [DocumentTransmissionController::class, 'transmettreSimple'])->name('dossiers.transmettre');
    Route::post('/dossiers/{dossier}/archiver', [ArchivageController::class, 'archiver'])->name('dossiers.archiver');

    Route::get('/document-types/{documentType}/checklist', [ChecklistController::class, 'parType'])->name('document-types.checklist');
    Route::get('/document-versions/{documentVersion}/checklist', [ChecklistController::class, 'show'])->name('document-versions.checklist');
    Route::post('/document-versions/{documentVersion}/checklist', [ChecklistController::class, 'store'])->name('document-versions.checklist.store');
});

// ============================================================
// ROUTES ÉVÉNEMENTS (accessibles à tous les espaces)
// ============================================================

Route::middleware(['auth', 'verified'])->prefix('evenements')->name('events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/data', [EventController::class, 'data'])->name('data');
    Route::get('/create', [EventController::class, 'create'])->name('create');
    Route::post('/', [EventController::class, 'store'])->name('store');
    Route::get('/{event}', [EventController::class, 'show'])->name('show');
    Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
    Route::put('/{event}', [EventController::class, 'update'])->name('update');
    Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
    Route::post('/{event}/statut', [EventController::class, 'updateStatut'])->name('statut');
});

// ============================================================
// ROUTES PROFIL
// ============================================================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
