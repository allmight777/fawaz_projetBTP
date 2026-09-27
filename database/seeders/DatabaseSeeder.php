<?php

namespace Database\Seeders;

use App\Models\Lot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // firstOrCreate partout (voir chaque seeder) : ce seeder peut être rejoué sans
        // erreur de clé unique, y compris sur une base de prod déjà peuplée — il ne fait
        // que compléter ce qui manque, jamais écraser des données existantes.
        Lot::firstOrCreate(
            ['code' => 'L01'],
            [
                'nom' => 'Lot 1',
                'description' => 'Lot 1 - Travaux de terrassement',
                'actif' => true,
            ]
        );

        $this->call([
            StructureSeeder::class,
            DocumentTypeSeeder::class,
            UserSeeder::class,
            GedTestAccountsSeeder::class,
        ]);
    }
}
