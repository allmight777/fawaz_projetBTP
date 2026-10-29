<?php

namespace Tests\Feature;

use App\Models\Dossier;
use App\Models\DocumentType;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ArchivageTest extends TestCase
{
    // Les migrations contiennent un trigger MySQL (SET NEW.x = NOW()) que SQLite refuse :
    // on rejoue les migrations sur la base mémoire des tests en ignorant ce trigger et les
    // ALTER propres à MySQL.
    protected function setUp(): void
    {
        parent::setUp();

        foreach (glob(database_path('migrations/*.php')) as $fichier) {
            if (str_contains(file_get_contents($fichier), 'CREATE TRIGGER')) {
                continue;
            }
            try {
                Artisan::call('migrate', ['--path' => $fichier, '--realpath' => true, '--force' => true]);
            } catch (\Throwable $e) {
                // ALTER ... MODIFY COLUMN ... ENUM (MySQL seulement) : sans objet sur SQLite
                // (les colonnes enum y sont de simples chaînes).
            }
        }
    }

    private function utilisateur(string $role, string $categorie, ?Structure $structure = null): User
    {
        static $n = 0;
        $n++;

        $user = User::create([
            'nom' => 'Test' . $n,
            'prenom' => 'User',
            'email' => "user{$n}@example.test",
            'password' => 'password',
            'role' => $role,
            'actif' => true,
            'categorie_role' => $categorie,
            'structure_id' => $structure?->id,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function dossier(User $emetteur, string $statut): Dossier
    {
        $type = DocumentType::firstOrCreate(
            ['code' => 'PLN'],
            ['nom' => 'Plan', 'categorie' => 'etude_execution', 'mode_traitement' => 'validation', 'actif' => true]
        );

        return Dossier::create([
            'identifiant' => 'PLN-' . uniqid(),
            'document_type_id' => $type->id,
            'structure_emettrice_id' => $emetteur->structure_id,
            'titre' => 'Doc test',
            'mode_traitement' => 'validation',
            'statut' => $statut,
            'version_courante' => 1,
            'cree_par' => $emetteur->id,
        ]);
    }

    public function test_seul_le_chef_du_bureau_de_controle_peut_archiver(): void
    {
        $entreprise = Structure::create(['nom' => 'Ent', 'type' => 'entreprise']);
        $chefEntreprise = $this->utilisateur(User::ROLE_ENTREPRISE_CHEF, 'responsable_organisme', $entreprise);
        $chefLot = $this->utilisateur(User::ROLE_CHEF_LOT, 'responsable_organisme');
        $dossier = $this->dossier($chefEntreprise, 'valide');

        // L'émetteur (même chef d'entreprise) ne peut pas archiver son propre document.
        $this->actingAs($chefEntreprise)->post(route('dossiers.archiver', $dossier))->assertForbidden();
        $this->assertSame('valide', $dossier->fresh()->statut);

        $this->actingAs($chefLot)->post(route('dossiers.archiver', $dossier))->assertRedirect();
        $this->assertSame('archive', $dossier->fresh()->statut);
    }

    public function test_un_dossier_archive_ne_peut_etre_ni_modifie_ni_supprime_ni_desarchive(): void
    {
        $entreprise = Structure::create(['nom' => 'Ent', 'type' => 'entreprise']);
        $emetteur = $this->utilisateur(User::ROLE_ENTREPRISE_COLLABORATEUR, 'collaborateur', $entreprise);
        $dossier = $this->dossier($emetteur, 'archive');

        // L'émetteur ne peut plus le supprimer
        $this->actingAs($emetteur)->delete(route('entreprise.dossiers.destroy', $dossier->id))->assertForbidden();
        $this->assertNotNull(Dossier::find($dossier->id));

        // Filet de sécurité du modèle : aucune modification, aucun désarchivage
        try {
            $dossier->update(['statut' => 'soumis']);
            $this->fail('Le dossier archivé a pu être modifié.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertSame('archive', $dossier->fresh()->statut);

        try {
            $dossier->delete();
            $this->fail('Le dossier archivé a pu être supprimé.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_archives_reservees_aux_chefs_des_autres_structures(): void
    {
        foreach ([
            ['entreprise', User::ROLE_ENTREPRISE_CHEF, User::ROLE_ENTREPRISE_COLLABORATEUR, 'entreprise.archives'],
            ['maitre_ouvrage', User::ROLE_MAITRE_OUVRAGE_CHEF, User::ROLE_MAITRE_OUVRAGE_COLLABORATEUR, 'maitre_ouvrage.archives'],
            ['bureau_etudes', User::ROLE_BUREAU_ETUDES_CHEF, User::ROLE_BUREAU_ETUDES_COLLABORATEUR, 'bureau_etudes.chef.archives'],
        ] as [$type, $roleChef, $roleCollab, $route]) {
            $structure = Structure::create(['nom' => $type, 'type' => $type]);
            $chef = $this->utilisateur($roleChef, 'responsable_organisme', $structure);
            $collab = $this->utilisateur($roleCollab, 'collaborateur', $structure);
            $this->dossier($chef, 'archive');

            $this->actingAs($chef)->get(route($route))->assertOk()->assertSee('Documents archivés');
            $this->actingAs($collab)->get(route($route))->assertForbidden();
        }
    }

    public function test_le_lien_archives_n_apparait_que_dans_le_menu_des_chefs(): void
    {
        foreach ([
            ['entreprise', User::ROLE_ENTREPRISE_CHEF, User::ROLE_ENTREPRISE_COLLABORATEUR, 'entreprise.chef.dashboard', 'entreprise.collaborateur.dashboard', 'entreprise.archives'],
            ['maitre_ouvrage', User::ROLE_MAITRE_OUVRAGE_CHEF, User::ROLE_MAITRE_OUVRAGE_COLLABORATEUR, 'maitre_ouvrage.chef.dashboard', 'maitre_ouvrage.collaborateur.dashboard', 'maitre_ouvrage.archives'],
        ] as [$type, $roleChef, $roleCollab, $dashChef, $dashCollab, $route]) {
            $structure = Structure::create(['nom' => $type, 'type' => $type]);
            $chef = $this->utilisateur($roleChef, 'responsable_organisme', $structure);
            $collab = $this->utilisateur($roleCollab, 'collaborateur', $structure);

            $this->actingAs($chef)->get(route($dashChef))->assertOk()->assertSee(route($route));
            $this->actingAs($collab)->get(route($dashCollab))->assertOk()->assertDontSee(route($route));
        }
    }

    public function test_la_case_archiver_de_la_diffusion_respecte_la_meme_regle(): void
    {
        $entreprise = Structure::create(['nom' => 'Ent', 'type' => 'entreprise']);
        $chefEntreprise = $this->utilisateur(User::ROLE_ENTREPRISE_CHEF, 'responsable_organisme', $entreprise);
        $dossier = $this->dossier($chefEntreprise, 'valide');

        $this->actingAs($chefEntreprise)
            ->post(route('cheflot.dossiers.diffusion.store', $dossier), ['archiver' => '1', 'structures' => [$entreprise->id]])
            ->assertForbidden();
        $this->assertSame('valide', $dossier->fresh()->statut);
    }
}
