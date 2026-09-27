<?php

namespace Database\Seeders;

use App\Models\Lot;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    // Comptes réels de l'équipe (extraits d'un export de production), pour éviter de les
    // recréer à la main sur chaque nouvel environnement. Les hash de mot de passe sont
    // repris tels quels : les personnes gardent leur mot de passe actuel. Idempotent
    // (firstOrCreate par email) : ne touche jamais un compte déjà présent, donc sans
    // risque pour une base de prod qui a déjà évolué (nouveaux comptes, changements de
    // mot de passe, etc.).
    public function run(): void
    {
        $lot1 = Lot::where('code', 'L01')->first();
        $entreprise = Structure::where('type', 'entreprise')->first();
        $bureauControle = Structure::where('type', 'bureau_controle')->first();
        $bureauEtudes = Structure::where('type', 'bureau_etudes')->first();
        $maitreOuvrage = Structure::where('type', 'maitre_ouvrage')->first();

        $users = [
            [
                'nom' => 'DUVAL', 'prenom' => 'Ange', 'email' => 'agoliganange15@gmail.com',
                'password' => '$2y$12$x8w.vf6AXLy1Xu2jgctoYOi2JwqxyV26oA7Z0iH8AgYHxAgcUpzFO',
                'role' => 'ENTREPRISE_COLLABORATEUR', 'lot_id' => $lot1?->id, 'structure_id' => $entreprise?->id,
                'categorie_role' => 'collaborateur', 'fonction' => 'Ingénieur Structure',
            ],
            [
                'nom' => 'DELAPOCHE', 'prenom' => 'Fawaz', 'email' => 'fawazcpro@gmail.com',
                'password' => '$2y$12$x.enDUoMdL1N0/R/Z7dZmeVoeQE6wCV2VxPuIEiK5EMJXs2LdZdNq',
                'role' => 'ENTREPRISE_CHEF', 'lot_id' => $lot1?->id, 'structure_id' => $entreprise?->id,
                'categorie_role' => 'responsable_organisme', 'fonction' => 'Directeur de Travaux',
            ],
            [
                'nom' => 'Fawaz', 'prenom' => '', 'email' => 'sagnide04@gmail.com',
                'password' => '$2y$12$j8GXnxhMUgqqlRPi7hJ.0u1irICSmctWJveb00NcTXbVGCg9yfksW',
                'role' => 'ADMIN', 'lot_id' => null, 'structure_id' => null,
                'categorie_role' => null, 'fonction' => null,
            ],
            [
                'nom' => 'LUC', 'prenom' => 'Agnide', 'email' => 'agnide37@gmail.com',
                'password' => '$2y$12$iubiVjSWuMlEDWvJZ/t1xet9t/1Tq47N.NwNXRwvHNVq9j/dDlwKq',
                'role' => 'BUREAU_CONTROLE_CHEF', 'lot_id' => $lot1?->id, 'structure_id' => $bureauControle?->id,
                'categorie_role' => 'responsable_organisme', 'fonction' => 'Chef de Mission',
            ],
            [
                'nom' => 'DUBOIS', 'prenom' => 'Bill', 'email' => 'billvrones@gmail.com',
                'password' => '$2y$12$UUMaChR5coArr8IAkQSh9OpnO3tyiFOIgrKzcc0G.5/zp0jbaPurm',
                'role' => 'BUREAU_CONTROLE_COLLABORATEUR', 'lot_id' => $lot1?->id, 'structure_id' => $bureauControle?->id,
                'categorie_role' => 'collaborateur', 'fonction' => 'Ingénieur Structure',
            ],
            [
                'nom' => 'ROLAND', 'prenom' => 'Ologo', 'email' => 'otiloni99@gmail.com',
                'password' => '$2y$12$tEsajRfbBRklIoymSBdgROdnZUE/.GxMx6HoDrxl7biLi90tmRqi6',
                'role' => 'MAITRE_OUVRAGE_CHEF', 'lot_id' => $lot1?->id, 'structure_id' => $maitreOuvrage?->id,
                'categorie_role' => 'responsable_organisme', 'fonction' => 'Responsable Projet',
            ],
            [
                'nom' => 'ADIGLA', 'prenom' => 'Euphrasie', 'email' => 'euphrasiee87@gmail.com',
                'password' => '$2y$12$NEyNIzEE6OHeTArfPQ2pgOOmcRcJU/1cLk/JbW/yKSp1vfZes4r6.',
                'role' => 'MAITRE_OUVRAGE_COLLABORATEUR', 'lot_id' => $lot1?->id, 'structure_id' => $maitreOuvrage?->id,
                'categorie_role' => 'collaborateur', 'fonction' => 'Assistante RP',
            ],
            [
                'nom' => 'YAMAL', 'prenom' => 'Lamine', 'email' => 'lamineexx@gmail.com',
                'password' => '$2y$12$hv9i3Dg3OzWGLgwoIUEPouqoMlQ1rCRmnW.WRj0ZPCDe8QdWw6b5K',
                'role' => 'BUREAU_ETUDES_CHEF', 'lot_id' => $lot1?->id, 'structure_id' => $bureauEtudes?->id,
                'categorie_role' => 'responsable_organisme', 'fonction' => 'Direction Technique',
            ],
            [
                'nom' => 'ROLLS', 'prenom' => 'Judicael', 'email' => 'judicaelmga@gmail.com',
                'password' => '$2y$12$i1tw01mfMPN5/l1LKCkWx.n.aaLYpCwYaUfF/BhQRqNrcNTo2uete',
                'role' => 'BUREAU_ETUDES_COLLABORATEUR', 'lot_id' => $lot1?->id, 'structure_id' => $bureauEtudes?->id,
                'categorie_role' => 'collaborateur', 'fonction' => "Ingénieur d'Etude",
            ],
            [
                'nom' => 'ABDOUL', 'prenom' => 'Ahmed', 'email' => 'ahmedhluxx@gmail.com',
                'password' => '$2y$12$pERVyRFosGcnyoHKdc4ITei6PspZSSW1VM0kBKvOSmn5WcqVY9k7O',
                'role' => 'BUREAU_CONTROLE_COLLABORATEUR', 'lot_id' => $lot1?->id, 'structure_id' => $bureauControle?->id,
                'categorie_role' => 'collaborateur', 'fonction' => 'Ingénieur HSE',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user + ['actif' => true, 'statut' => 'actif', 'email_verified_at' => now()]
            );
        }
    }
}
