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
                'role' => User::ROLE_ENTREPRISE_CHEF,
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
                'role' => User::ROLE_MAITRE_OUVRAGE_CHEF,
                'structure_id' => $maitreOuvrage->id,
                'categorie_role' => 'responsable_organisme',
                'fonction' => 'Directeur Technique',
                'actif' => true,
                'email_verified_at' => now(),
            ]
        );

        // Maître d'Œuvre : un responsable et un collaborateur pour tester le circuit de visa à trois niveaux.
        $maitreOeuvre = Structure::where('type', 'maitre_oeuvre')->first();
        if ($maitreOeuvre) {
            foreach ([
                ['moe.chef@fawaz.local', 'Chef', User::ROLE_MAITRE_OEUVRE_CHEF, 'responsable_organisme', "Responsable Maître d'Œuvre"],
                ['moe.collab@fawaz.local', 'Collaborateur', User::ROLE_MAITRE_OEUVRE_COLLABORATEUR, 'collaborateur', "Ingénieur Maître d'Œuvre"],
            ] as [$email, $prenom, $role, $categorie, $fonction]) {
                User::firstOrCreate(
                    ['email' => $email],
                    [
                        'nom' => 'MOE',
                        'prenom' => $prenom,
                        'password' => Hash::make('password'),
                        'role' => $role,
                        'structure_id' => $maitreOeuvre->id,
                        'categorie_role' => $categorie,
                        'fonction' => $fonction,
                        'actif' => true,
                        'email_verified_at' => now(),
                    ]
                );
            }
        }
    }
}
