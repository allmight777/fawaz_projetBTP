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

    public function test_l_emetteur_archive_son_document_et_un_simple_collegue_non(): void
    {
        $entreprise = Structure::create(['nom' => 'Ent', 'type' => 'entreprise']);
        $chefEntreprise = $this->utilisateur(User::ROLE_ENTREPRISE_CHEF, 'responsable_organisme', $entreprise);
        $collegue = $this->utilisateur(User::ROLE_ENTREPRISE_COLLABORATEUR, 'collaborateur', $entreprise);
        $autreStructure = Structure::create(['nom' => 'BE', 'type' => 'bureau_etudes']);
        $chefAutre = $this->utilisateur(User::ROLE_BUREAU_ETUDES_CHEF, 'responsable_organisme', $autreStructure);

        $dossier = $this->dossier($chefEntreprise, 'valide');

        // Ni un collègue qui n'est pas l'émetteur, ni le chef d'une structure à qui le document n'a pas été transmis.
        $this->actingAs($collegue)->post(route('dossiers.archiver', $dossier))->assertForbidden();
        $this->actingAs($chefAutre)->post(route('dossiers.archiver', $dossier))->assertForbidden();
        $this->assertSame('valide', $dossier->fresh()->statut);

        // L'émetteur archive, quel que soit son rôle : le document quitte « Mes documents ».
        $this->actingAs($chefEntreprise)->post(route('dossiers.archiver', $dossier))->assertRedirect(route('archives.index'));
        $this->assertSame('archive', $dossier->fresh()->statut);
    }

    public function test_un_document_en_cours_de_visa_ne_s_archive_pas(): void
    {
        $entreprise = Structure::create(['nom' => 'Ent', 'type' => 'entreprise']);
        $chef = $this->utilisateur(User::ROLE_ENTREPRISE_CHEF, 'responsable_organisme', $entreprise);
        $dossier = $this->dossier($chef, 'en_cours');

        $this->actingAs($chef)->post(route('dossiers.archiver', $dossier))->assertRedirect();
        $this->assertSame('en_cours', $dossier->fresh()->statut);
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

    public function test_chacun_voit_ses_archives_les_collaborateurs_seulement_celles_de_leur_structure(): void
    {
        $ent = Structure::create(['nom' => 'Entreprise Alpha', 'type' => 'entreprise']);
        $be = Structure::create(['nom' => 'BE Beta', 'type' => 'bureau_etudes']);
        $chefEnt = $this->utilisateur(User::ROLE_ENTREPRISE_CHEF, 'responsable_organisme', $ent);
        $collabEnt = $this->utilisateur(User::ROLE_ENTREPRISE_COLLABORATEUR, 'collaborateur', $ent);
        $chefBe = $this->utilisateur(User::ROLE_BUREAU_ETUDES_CHEF, 'responsable_organisme', $be);
        $collabBe = $this->utilisateur(User::ROLE_BUREAU_ETUDES_COLLABORATEUR, 'collaborateur', $be);

        $archive = $this->dossier($chefEnt, 'archive');

        // Archive de l'entreprise : visible par toute l'entreprise, pas par le BE tant qu'elle ne lui est pas partagée.
        $this->actingAs($chefEnt)->get(route('archives.index'))->assertOk()->assertSee($archive->identifiant);
        $this->actingAs($collabEnt)->get(route('archives.index'))->assertOk()->assertSee($archive->identifiant);
        $this->actingAs($chefBe)->get(route('archives.index'))->assertOk()->assertDontSee($archive->identifiant);

        // L'entreprise a transmis le document au BE : son chef le retrouve dans ses archives, pas son collaborateur.
        $transmission = \App\Models\DocumentTransmission::create([
            'dossier_id' => $archive->id, 'emetteur_id' => $chefEnt->id, 'mode' => 'simple', 'date_transmission' => now(),
        ]);
        \App\Models\TransmissionDestinataire::create(['document_transmission_id' => $transmission->id, 'structure_id' => $be->id]);

        $this->actingAs($chefBe)->get(route('archives.index'))->assertOk()->assertSee($archive->identifiant);
        $this->actingAs($collabBe)->get(route('archives.index'))->assertOk()->assertDontSee($archive->identifiant);
    }

    public function test_le_lien_archives_est_dans_le_menu_de_tous_les_espaces(): void
    {
        foreach ([
            ['entreprise', User::ROLE_ENTREPRISE_CHEF, User::ROLE_ENTREPRISE_COLLABORATEUR, 'entreprise.chef.dashboard', 'entreprise.collaborateur.dashboard'],
            ['maitre_ouvrage', User::ROLE_MAITRE_OUVRAGE_CHEF, User::ROLE_MAITRE_OUVRAGE_COLLABORATEUR, 'maitre_ouvrage.chef.dashboard', 'maitre_ouvrage.collaborateur.dashboard'],
        ] as [$type, $roleChef, $roleCollab, $dashChef, $dashCollab]) {
            $structure = Structure::create(['nom' => $type, 'type' => $type]);
            $chef = $this->utilisateur($roleChef, 'responsable_organisme', $structure);
            $collab = $this->utilisateur($roleCollab, 'collaborateur', $structure);

            $this->actingAs($chef)->get(route($dashChef))->assertOk()->assertSee(route('archives.index'));
            $this->actingAs($collab)->get(route($dashCollab))->assertOk()->assertSee(route('archives.index'));
        }
    }

    public function test_la_diffusion_reste_reservee_a_l_espace_de_controle(): void
    {
        $entreprise = Structure::create(['nom' => 'Ent', 'type' => 'entreprise']);
        $chefEntreprise = $this->utilisateur(User::ROLE_ENTREPRISE_CHEF, 'responsable_organisme', $entreprise);
        $dossier = $this->dossier($chefEntreprise, 'valide');

        $this->actingAs($chefEntreprise)
            ->post(route('cheflot.dossiers.diffusion.store', $dossier), ['archiver' => '1', 'structures' => [$entreprise->id]]);
        // La diffusion appartient à l'espace du Bureau de Contrôle : refusée à l'entreprise, rien n'est archivé.
        $this->assertSame('valide', $dossier->fresh()->statut);
    }
}
