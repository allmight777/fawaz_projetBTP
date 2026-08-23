<?php

namespace Database\Seeders;

use App\Models\Structure;
use Illuminate\Database\Seeder;

class StructureSeeder extends Seeder
{
    public function run(): void
    {
        $structures = [
            ['nom' => 'Entreprise', 'type' => 'entreprise'],
            ['nom' => 'Bureau de Contrôle', 'type' => 'bureau_controle'],
            ['nom' => "Bureau d'Études", 'type' => 'bureau_etudes'],
            ['nom' => "Maître d'Ouvrage Délégué", 'type' => 'maitre_ouvrage'],
        ];

        foreach ($structures as $structure) {
            Structure::firstOrCreate(['type' => $structure['type']], $structure + ['actif' => true]);
        }
    }
}
