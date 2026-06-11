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
        // 1. Créer les lots
        $lot1 = Lot::create([
            'nom' => 'Lot 1',
            'code' => 'L01',
            'description' => 'Lot 1 - Travaux de terrassement',
            'actif' => true,
        ]);

        $lot2 = Lot::create([
            'nom' => 'Lot 2',
            'code' => 'L02',
            'description' => 'Lot 2 - Gros œuvre',
            'actif' => true,
        ]);

        $lot3 = Lot::create([
            'nom' => 'Lot 3',
            'code' => 'L03',
            'description' => 'Lot 3 - Couverture étanchéité',
            'actif' => true,
        ]);

        $lot4 = Lot::create([
            'nom' => 'Lot 4',
            'code' => 'L04',
            'description' => 'Lot 4 - Menuiserie extérieure',
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

        // Controleur Lot 2
        User::create([
            'nom' => 'Fawaz',
            'prenom' => 'Ingénieur',
            'email' => 'fawazcpro@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'CONTROLEUR',
            'lot_id' => $lot2->id,
            'actif' => true,
            'email_verified_at' => now(),
        ]);

        // Controleur Lot 3
        User::create([
            'nom' => 'Agnide',
            'prenom' => 'Ingénieur',
            'email' => 'agnide37@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'CONTROLEUR',
            'lot_id' => $lot3->id,
            'actif' => true,
            'email_verified_at' => now(),
        ]);

        // Controleur Lot 4 (inactif)
        User::create([
            'nom' => 'Otilo',
            'prenom' => 'Ingénieur',
            'email' => 'otiloni99@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'CONTROLEUR',
            'lot_id' => $lot4->id,
            'actif' => false,
            'email_verified_at' => now(),
        ]);
    }
}
