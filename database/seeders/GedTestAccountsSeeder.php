<?php

namespace Database\Seeders;

use App\Models\Structure;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GedTestAccountsSeeder extends Seeder
{
    // Comptes de test pour valider le pipeline GED avec les vrais rôles du cahier des
    // charges (Entreprise, Maître d'Ouvrage), en plus des rôles ADMIN/CHEF LOT/CONTROLEUR
    // déjà couverts par le seeder existant. Le champ `role` reste 'CONTROLEUR' (valeur la
    // moins privilégiée de l'enum existant, cf. Phase 1 : on ne touche pas à cette colonne) ;
    // l'autorisation réelle vient de `structure_id` / `cree_par`.
    public function run(): void
    {
        $entreprise = Structure::where('type', 'entreprise')->firstOrFail();
        $maitreOuvrage = Structure::where('type', 'maitre_ouvrage')->firstOrFail();

        User::firstOrCreate(
            ['email' => 'entreprise.test@fawaz.local'],
            [
                'nom' => 'Test',
                'prenom' => 'Entreprise',
                'password' => Hash::make('password'),
                'role' => 'CONTROLEUR',
                'structure_id' => $entreprise->id,
                'categorie_role' => 'responsable_organisme',
                'fonction' => 'Directeur des Travaux',
                'actif' => true,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'mo.test@fawaz.local'],
            [
                'nom' => 'Test',
                'prenom' => 'Maître d\'Ouvrage',
                'password' => Hash::make('password'),
                'role' => 'CONTROLEUR',
                'structure_id' => $maitreOuvrage->id,
                'categorie_role' => 'responsable_organisme',
                'fonction' => 'Directeur Technique',
                'actif' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
