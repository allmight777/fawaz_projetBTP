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
        $lot1 = Lot::create([
            'nom' => 'Lot 1',
            'code' => 'L01',
            'description' => 'Lot 1 - Travaux de terrassement',
            'actif' => true,
        ]);



        // 2. Créer les utilisateurs
        // Admin
        User::create([
            'nom' => 'Fawaz',
            'prenom' => '',
            'email' => 'sagnide04@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'ADMIN',
            'lot_id' => null,
            'actif' => true,
            'email_verified_at' => now(),
        ]);

        // Chef Lot
        User::create([
            'nom' => 'Ingénieur Chef',
            'prenom' => '',
            'email' => 'billvrones@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'CHEF LOT',
            'lot_id' => null,
            'actif' => true,
            'email_verified_at' => now(),
        ]);

        // Controleur Lot 1
        User::create([
            'nom' => 'Olagnika',
            'prenom' => 'Ingénieur',
            'email' => 'olagnikafawaz@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'CONTROLEUR',
            'lot_id' => $lot1->id,
            'actif' => true,
            'email_verified_at' => now(),
        ]);



    }
}
