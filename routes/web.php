<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChefLotController;
use App\Http\Controllers\ControleurController;
use App\Http\Controllers\DemandeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

// Dashboard par défaut
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'ADMIN') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'CHEF LOT') {
        return redirect()->route('cheflot.dashboard');
    } else {
        return redirect()->route('controleur.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Routes pour ADMIN
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
});

// Routes pour DEMANDES (création)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/demandes/create', [DemandeController::class, 'create'])->name('demandes.create');
    Route::post('/demandes', [DemandeController::class, 'store'])->name('demandes.store');
    Route::get('/demandes/{demande}/telecharger', [DemandeController::class, 'telechargerFichier'])->name('demandes.telecharger');
});

// Routes pour CHEF LOT
Route::middleware(['auth', 'verified', 'role:CHEF LOT'])->prefix('cheflot')->name('cheflot.')->group(function () {
    Route::get('/dashboard', [ChefLotController::class, 'dashboard'])->name('dashboard');
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
    // Historique global
Route::get('/historique', [ChefLotController::class, 'historiqueGlobal'])->name('historique.global');
Route::get('/historique/export', [ChefLotController::class, 'exportHistorique'])->name('historique.export');
});

// Routes pour CONTROLEUR
Route::middleware(['auth', 'verified', 'role:CONTROLEUR'])->prefix('controleur')->name('controleur.')->group(function () {
    Route::get('/dashboard', [ControleurController::class, 'dashboard'])->name('dashboard');
    Route::get('/mon-lot', [ControleurController::class, 'monLot'])->name('mon-lot');
    Route::get('/taches', [ControleurController::class, 'taches'])->name('taches');
});

// Routes profil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
