<?php

namespace Database\Seeders;

use App\Models\Lot;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // GED — structures et types de documents (indépendants des lots/utilisateurs existants)
        $this->call([
            StructureSeeder::class,
            DocumentTypeSeeder::class,
            GedTestAccountsSeeder::class,
        ]);

        // 1. Créer les lots
        // firstOrCreate (comme les autres seeders) : ce seeder est rejoué à chaque
        // démarrage du conteneur (migrate:fresh --seed au boot), donc il doit rester
        // rejouable sans erreur de clé unique si la ligne existe déjà.
        $lot1 = Lot::firstOrCreate(
            ['code' => 'L01'],
            [
                'nom' => 'Lot 1',
                'description' => 'Lot 1 - Travaux de terrassement',
                'actif' => true,
            ]
        );

        // 2. Créer les utilisateurs
        // Admin
        User::firstOrCreate(
            ['email' => 'sagnide04@gmail.com'],
            [
                'nom' => 'Fawaz',
                'prenom' => '',
                'password' => Hash::make('password'),
                'role' => 'ADMIN',
                'lot_id' => null,
                'actif' => true,
                'email_verified_at' => now(),
            ]
        );

        // Chef Lot
        User::firstOrCreate(
            ['email' => 'billvrones@gmail.com'],
            [
                'nom' => 'Ingénieur Chef',
                'prenom' => '',
                'password' => Hash::make('password'),
                'role' => 'CHEF LOT',
                'lot_id' => null,
                'actif' => true,
                'email_verified_at' => now(),
            ]
        );

        // Controleur Lot 1
        User::firstOrCreate(
            ['email' => 'olagnikafawaz@gmail.com'],
            [
                'nom' => 'Olagnika',
                'prenom' => 'Ingénieur',
                'password' => Hash::make('password'),
                'role' => 'CONTROLEUR',
                'lot_id' => $lot1->id,
                'actif' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
