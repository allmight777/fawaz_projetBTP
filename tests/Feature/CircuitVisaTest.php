<?php

namespace Tests\Feature;

use App\Models\CircuitVisa;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Dossier;
use App\Models\Structure;
use App\Models\User;
use App\Services\CircuitVisaService;
use App\Support\Espaces;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Circuit de visa : l'émetteur choisit les structures (BC, BE, MOE) ; chacune vise à son niveau. */
class CircuitVisaTest extends TestCase
{
    private array $structures = [];
    private array $chefs = [];
    private array $collabs = [];
    private User $collabMoe;

    protected function setUp(): void
    {
        parent::setUp();

        // Mêmes contournements que ArchivageTest : triggers MySQL et ALTER ... MODIFY inutilisables sur SQLite.
        foreach (glob(database_path('migrations/*.php')) as $fichier) {
            if (str_contains(file_get_contents($fichier), 'CREATE TRIGGER')) {
                continue;
            }
            try {
                Artisan::call('migrate', ['--path' => $fichier, '--realpath' => true, '--force' => true]);
            } catch (\Throwable $e) {
            }
        }

        // Colonnes ENUM de MySQL devenues texte libre par des migrations que SQLite ne sait pas rejouer.
        Schema::table('dossiers', fn (Blueprint $t) => $t->string('statut', 30)->default('brouillon')->change());
        Schema::table('document_transmissions', fn (Blueprint $t) => $t->string('mode', 20)->default('simple')->change());
        Schema::table('document_types', fn (Blueprint $t) => $t->string('categorie', 50)->change());

        Mail::fake();
        Storage::fake('public');

        $roles = [
            'entreprise' => [User::ROLE_ENTREPRISE_CHEF, User::ROLE_ENTREPRISE_COLLABORATEUR],
            'bureau_controle' => [User::ROLE_BUREAU_CONTROLE_CHEF, User::ROLE_BUREAU_CONTROLE_COLLABORATEUR],
            'bureau_etudes' => [User::ROLE_BUREAU_ETUDES_CHEF, User::ROLE_BUREAU_ETUDES_COLLABORATEUR],
            'maitre_oeuvre' => [User::ROLE_MAITRE_OEUVRE_CHEF, User::ROLE_MAITRE_OEUVRE_COLLABORATEUR],
        ];
        foreach ($roles as $type => [$roleChef, $roleCollab]) {
            $this->structures[$type] = Structure::create(['nom' => $type, 'type' => $type]);
            $this->chefs[$type] = $this->utilisateur($roleChef, 'responsable_organisme', $this->structures[$type]);
            $this->collabs[$type] = $this->utilisateur($roleCollab, 'collaborateur', $this->structures[$type]);
        }
        $this->collabMoe = $this->collabs['maitre_oeuvre'];
    }

    private function utilisateur(string $role, string $categorie, Structure $structure): User
    {
        static $n = 0;
        $n++;

        $user = User::create([
            'nom' => 'Test' . $n, 'prenom' => 'User', 'email' => "circuit{$n}@example.test", 'password' => 'password',
            'role' => $role, 'actif' => true, 'categorie_role' => $categorie, 'structure_id' => $structure->id,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    /** Un dossier émis par l'entreprise, avec le circuit de visa ouvert vers les structures données. */
    private function dossierEnCircuit(array $types): array
    {
        $type = DocumentType::firstOrCreate(
            ['code' => 'PLN'],
            ['nom' => 'Plan', 'categorie' => 'etude_execution', 'mode_traitement' => 'validation', 'actif' => true]
        );
        $emetteur = $this->chefs['entreprise'];
        $dossier = Dossier::create([
            'identifiant' => 'PLN-' . uniqid(), 'titre' => 'Plan test', 'document_type_id' => $type->id,
            'structure_emettrice_id' => $emetteur->structure_id, 'mode_traitement' => 'validation',
            'version_courante' => 1, 'cree_par' => $emetteur->id, 'statut' => 'brouillon',
        ]);
        $version = DocumentVersion::create([
            'dossier_id' => $dossier->id, 'numero_version' => 1, 'fichier_nom' => 'plan.pdf', 'fichier_chemin' => 'documents/plan.pdf',
            'importe_par' => $emetteur->id, 'date_import' => now(), 'statut' => 'soumis',
        ]);
        app(CircuitVisaService::class)->ouvrir($dossier, $version, $emetteur, $types);

        return [$dossier->fresh(), $version->fresh()];
    }

    /** Cachet mémorisé + signature dessinée, tels que le formulaire de visa les envoie. */
    private function pieceVisa(User $user): array
    {
        Storage::disk('public')->put('cachets/' . $user->id . '/cachet.png', $this->png());
        $user->forceFill(['cachet_chemin' => 'cachets/' . $user->id . '/cachet.png'])->save();

        return ['signature' => 'data:image/png;base64,' . base64_encode($this->png())];
    }

    private function png(): string
    {
        $image = imagecreatetruecolor(40, 40);
        imagefill($image, 0, 0, imagecolorallocate($image, 0, 0, 0));
        ob_start();
        imagepng($image);

        return ob_get_clean();
    }

    public function test_le_circuit_ouvre_une_ligne_par_structure_et_chaque_responsable_recoit_le_document(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle', 'bureau_etudes', 'maitre_oeuvre']);

        $this->assertSame(3, CircuitVisa::where('document_version_id', $version->id)->count());
        $this->assertSame('en_cours', $dossier->statut);
        $this->assertSame('BC,BE,MOE', $dossier->structure_cible);

        foreach (['bureau_controle' => 'cheflot.documents.recus', 'bureau_etudes' => 'bureau_etudes.chef.documents.recus', 'maitre_oeuvre' => 'maitre_oeuvre.chef.documents.recus'] as $type => $route) {
            $this->actingAs($this->chefs[$type])->get(route($route))->assertOk()->assertSee($dossier->identifiant);
        }
    }

    public function test_le_document_est_valide_des_le_premier_visa_et_les_autres_peuvent_encore_viser(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle', 'bureau_etudes', 'maitre_oeuvre']);

        $routes = [
            'bureau_controle' => 'cheflot.documents.recus.decision',
            'bureau_etudes' => 'bureau_etudes.chef.documents.recus.decision',
            'maitre_oeuvre' => 'maitre_oeuvre.chef.documents.recus.decision',
        ];

        foreach (['bureau_controle', 'bureau_etudes'] as $type) {
            $chef = $this->chefs[$type];
            $this->actingAs($chef)
                ->post(route($routes[$type], $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chef))
                ->assertSessionHasNoErrors()->assertRedirect();

            // Validé dès le premier visa ; seules les structures qui ont visé apparaissent « Visé ».
            $this->assertSame('valide', $dossier->fresh()->statut, "Pas validé après le visa $type");
            $this->assertSame('valide', CircuitVisa::where('document_version_id', $version->id)->where('structure_type', $type)->value('statut'));
        }

        $resume = collect($dossier->fresh()->load('circuitVisas')->visas_resume)->pluck('statut', 'sigle')->all();
        $this->assertSame(['BC' => 'valide', 'BE' => 'valide', 'MOE' => 'en_attente'], $resume);

        $chefMoe = $this->chefs['maitre_oeuvre'];
        $this->actingAs($chefMoe)
            ->post(route($routes['maitre_oeuvre'], $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chefMoe))
            ->assertSessionHasNoErrors();

        $this->assertSame('valide', $dossier->fresh()->statut);
        $this->assertSame('valide', $version->fresh()->statut);
        $this->assertSame(3, $version->visas()->where('type', 'validation')->count());
    }

    public function test_une_correction_demandee_par_une_structure_met_le_document_a_corriger_et_fige_les_autres(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle', 'maitre_oeuvre']);

        $this->actingAs($this->chefs['maitre_oeuvre'])
            ->post(route('maitre_oeuvre.chef.documents.recus.decision', $dossier->id), ['decision' => 'a_corriger', 'commentaires' => 'Cotes manquantes'])
            ->assertSessionHasNoErrors();

        $this->assertSame('a_corriger', $dossier->fresh()->statut);

        $chefBc = $this->chefs['bureau_controle'];
        $this->actingAs($chefBc)
            ->post(route('cheflot.documents.recus.decision', $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chefBc))
            ->assertForbidden();
    }

    public function test_une_structure_hors_circuit_ne_peut_pas_decider(): void
    {
        [$dossier] = $this->dossierEnCircuit(['bureau_controle']);

        $this->actingAs($this->chefs['bureau_etudes'])
            ->post(route('bureau_etudes.chef.documents.recus.decision', $dossier->id), ['decision' => 'a_corriger', 'commentaires' => 'x'])
            ->assertNotFound(); // le document ne lui a pas été adressé : il ne le voit même pas
        $this->assertSame('en_cours', $dossier->fresh()->statut);
    }

    public function test_le_maitre_d_oeuvre_affecte_son_collaborateur_qui_analyse_puis_le_chef_vise(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['maitre_oeuvre']);
        $chef = $this->chefs['maitre_oeuvre'];

        // Le chef affecte un collaborateur de SA structure
        $this->actingAs($chef)->get(route('maitre_oeuvre.chef.dossiers.affecter', $version))->assertOk()->assertSee($this->collabMoe->full_name);
        $this->actingAs($chef)->post(route('maitre_oeuvre.chef.dossiers.affecter.store', $version), ['controleurs' => [$this->collabMoe->id]])
            ->assertSessionHasNoErrors();

        $this->assertSame('en_analyse', CircuitVisa::where('document_version_id', $version->id)->value('statut'));

        // Il ne peut pas affecter un collaborateur d'une autre structure
        $autre = $this->collabs['bureau_controle'];
        $this->actingAs($chef)->post(route('maitre_oeuvre.chef.dossiers.affecter.store', $version), ['controleurs' => [$autre->id]])->assertForbidden();

        // La page « document reçu » liste l'analyse du collaborateur affecté
        $this->actingAs($chef)->get(route('maitre_oeuvre.chef.documents.recus.show', $dossier->id))->assertOk()->assertSee('Analyses des collaborateurs');

        // Le collaborateur voit son affectation et termine son analyse dans SON espace
        $affectation = $this->collabMoe->affectations()->first();
        $this->actingAs($this->collabMoe)->get(route('maitre_oeuvre.collaborateur.dashboard'))->assertOk();
        $this->actingAs($this->collabMoe)->get(route('maitre_oeuvre.collaborateur.affectations.show', $affectation))->assertOk();
        $this->actingAs($this->collabMoe)->post(route('maitre_oeuvre.collaborateur.affectations.terminer', $affectation), ['contenu' => 'RAS'])
            ->assertRedirect(route('maitre_oeuvre.collaborateur.affectations.index'));

        // Le chef consolide puis vise : seule structure du circuit → document validé
        $this->actingAs($chef)->get(route('maitre_oeuvre.chef.dossiers.decision', $version))->assertOk();
        $this->actingAs($chef)->post(route('maitre_oeuvre.chef.dossiers.decision.store', $version), ['decision' => 'valide'] + $this->pieceVisa($chef))
            ->assertSessionHasNoErrors();

        $this->assertSame('valide', $dossier->fresh()->statut);
    }

    public function test_les_routes_suivent_l_espace_de_l_utilisateur_et_les_espaces_sont_cloisonnes(): void
    {
        $this->assertSame('maitre_oeuvre.chef.dashboard', Espaces::nomRoute('cheflot.dashboard', $this->chefs['maitre_oeuvre']));
        $this->assertSame('maitre_oeuvre.collaborateur.affectations.index', Espaces::nomRoute('controleur.affectations.index', $this->collabMoe));
        $this->assertSame('bureau_etudes.chef.documents.recus', Espaces::nomRoute('cheflot.documents.recus', $this->chefs['bureau_etudes']));
        $this->assertSame('cheflot.dashboard', Espaces::nomRoute('cheflot.dashboard', $this->chefs['bureau_controle']));

        // Le MOE n'entre pas dans l'espace du Bureau d'Études ni du Bureau de Contrôle
        $this->actingAs($this->chefs['maitre_oeuvre'])->get(route('bureau_etudes.chef.dashboard'))->assertRedirect(route('maitre_oeuvre.chef.dashboard'));
        $this->actingAs($this->chefs['maitre_oeuvre'])->get(route('cheflot.dashboard'))->assertRedirect(route('maitre_oeuvre.chef.dashboard'));
        $this->actingAs($this->collabMoe)->get(route('maitre_oeuvre.chef.dashboard'))->assertRedirect(route('maitre_oeuvre.collaborateur.dashboard'));

        // Chaque tableau de bord s'affiche, avec la couleur violette pour le MOE
        $this->actingAs($this->chefs['maitre_oeuvre'])->get(route('maitre_oeuvre.chef.dashboard'))->assertOk()->assertSee('--accent: #8B6FD6', false);
        $this->actingAs($this->chefs['bureau_etudes'])->get(route('bureau_etudes.chef.dashboard'))->assertOk()->assertDontSee('--accent: #8B6FD6', false);
    }

    public function test_une_nouvelle_version_rouvre_le_meme_circuit(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle', 'maitre_oeuvre']);
        $dossier->update(['statut' => 'a_corriger']);

        $v2 = DocumentVersion::create([
            'dossier_id' => $dossier->id, 'numero_version' => 2, 'fichier_nom' => 'plan2.pdf', 'fichier_chemin' => 'documents/plan2.pdf',
            'importe_par' => $this->chefs['entreprise']->id, 'date_import' => now(), 'statut' => 'soumis',
        ]);
        app(CircuitVisaService::class)->rouvrir($dossier, $v2, $this->chefs['entreprise']);

        $this->assertEqualsCanonicalizing(['bureau_controle', 'maitre_oeuvre'], CircuitVisa::where('document_version_id', $v2->id)->pluck('structure_type')->all());
        $this->assertSame('en_cours', $dossier->fresh()->statut);
    }

    public function test_toutes_les_pages_des_espaces_be_et_moe_s_affichent(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle', 'bureau_etudes', 'maitre_oeuvre']);

        foreach (['bureau_etudes' => 'bureau_etudes', 'maitre_oeuvre' => 'maitre_oeuvre'] as $type => $prefixe) {
            $chef = $this->chefs[$type];
            foreach ([
                "$prefixe.chef.dashboard" => [],
                "$prefixe.chef.documents.recus" => [],
                "$prefixe.chef.documents.recus.show" => [$dossier->id],
                "$prefixe.chef.statistiques" => [],
                "$prefixe.chef.controleurs" => [],
                "$prefixe.chef.archives.index" => [],
                "$prefixe.chef.dossiers.affecter" => [$version],
                'dossiers.show' => [$dossier],
                'dossiers.index' => [],
                'collaborateurs.index' => [],
                'events.index' => [],
            ] as $route => $parametres) {
                $this->actingAs($chef)->get(route($route, $parametres))->assertOk();
            }

            $collab = $this->collabs[$type];
            foreach (["$prefixe.collaborateur.dashboard" => [], "$prefixe.collaborateur.affectations.index" => [], 'dossiers.index' => []] as $route => $parametres) {
                $this->actingAs($collab)->get(route($route, $parametres))->assertOk();
            }
        }

        // L'émetteur voit la colonne Visas et le circuit
        $this->actingAs($this->chefs['entreprise'])->get(route('entreprise.dossiers'))->assertOk()->assertSee('BC')->assertSee('MOE');
        $this->actingAs($this->chefs['entreprise'])->get(route('entreprise.dossiers.show', $dossier->id))->assertOk()->assertSee('Circuit de visa');
    }

    public function test_le_chef_d_entreprise_envoie_en_circuit_de_visa_vers_plusieurs_structures(): void
    {
        $type = DocumentType::create(['code' => 'PLN', 'nom' => 'Plan', 'categorie' => 'etude_execution', 'mode_traitement' => 'validation', 'actif' => true]);
        $chef = $this->chefs['entreprise'];
        $donnees = [
            'fichiers' => [UploadedFile::fake()->create('plan.pdf', 100, 'application/pdf')],
            'document_type_id' => [$type->id], 'titre' => ['Plan A'], 'mode' => ['validation'],
        ] + $this->pieceVisa($chef);

        // Sans structure choisie : refusé
        $this->actingAs($chef)->post(route('entreprise.transmettre'), $donnees)->assertSessionHasErrors('circuit');
        $this->assertSame(0, Dossier::count());

        $donnees['circuit'] = [['bureau_controle', 'maitre_oeuvre']];
        $this->actingAs($chef)->post(route('entreprise.transmettre'), $donnees)->assertSessionHasNoErrors();

        $dossier = Dossier::firstOrFail();
        $this->assertSame('en_cours', $dossier->statut);
        $this->assertEqualsCanonicalizing(['bureau_controle', 'maitre_oeuvre'], $dossier->circuitVisas->pluck('structure_type')->all());
    }

    public function test_le_collaborateur_propose_les_structures_et_son_chef_vise_puis_envoie(): void
    {
        $type = DocumentType::create(['code' => 'PLN', 'nom' => 'Plan', 'categorie' => 'etude_execution', 'mode_traitement' => 'validation', 'actif' => true]);
        $collab = $this->collabs['entreprise'];
        $chef = $this->chefs['entreprise'];

        $this->actingAs($collab)->post(route('entreprise.transmettre'), [
            'fichiers' => [UploadedFile::fake()->create('plan.pdf', 100, 'application/pdf')],
            'document_type_id' => [$type->id], 'titre' => ['Plan B'], 'mode' => ['validation'], 'circuit' => [['bureau_etudes']],
        ])->assertSessionHasNoErrors();

        $dossier = Dossier::firstOrFail();
        $this->assertTrue((bool) $dossier->necessite_transfert_chef);
        $this->assertSame(['bureau_etudes'], $dossier->structures_souhaitees);
        $this->assertSame(0, $dossier->circuitVisas()->count());

        $this->actingAs($chef)->get(route('entreprise.documents.a-transferer'))->assertOk()->assertSee('Circuit de visa');

        // Le chef corrige le choix : BC + MOE, en un seul envoi
        $this->actingAs($chef)->post(route('entreprise.dossiers.transferer', $dossier->id), ['structures' => ['bureau_controle', 'maitre_oeuvre']] + $this->pieceVisa($chef))
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing(['bureau_controle', 'maitre_oeuvre'], $dossier->fresh()->circuitVisas->pluck('structure_type')->all());
        $this->assertFalse((bool) $dossier->fresh()->necessite_transfert_chef);
    }

    public function test_les_chiffres_du_tableau_de_bord_entreprise_sont_ceux_des_documents_reels(): void
    {
        $type = DocumentType::create(['code' => 'LPO', 'nom' => 'Liste', 'categorie' => 'etude_execution', 'mode_traitement' => 'validation', 'actif' => true]);
        $collab = $this->collabs['entreprise'];

        // Un document soumis par le collaborateur, en attente de transfert chez le chef
        $this->actingAs($collab)->post(route('entreprise.transmettre'), [
            'fichiers' => [UploadedFile::fake()->create('liste.pdf', 50, 'application/pdf')],
            'document_type_id' => [$type->id], 'titre' => ['kkkk'], 'mode' => ['validation'], 'circuit' => [['bureau_controle']],
        ])->assertSessionHasNoErrors();

        // Le chef (qui n'a rien importé lui-même) voit bien 1 document en attente, à transférer
        $this->actingAs($this->chefs['entreprise'])->get(route('entreprise.chef.dashboard'))
            ->assertOk()
            ->assertViewHas('documentsEnAttente', 1)
            ->assertViewHas('documentsATransferer', 1)
            ->assertViewHas('documentsValides', 0);

        $this->actingAs($collab)->get(route('entreprise.collaborateur.dashboard'))
            ->assertOk()->assertViewHas('mesDocuments', 1)->assertViewHas('documentsATransferer', 1);
    }

    public function test_les_chiffres_d_une_structure_visante_suivent_son_propre_visa(): void
    {
        [$dossier] = $this->dossierEnCircuit(['bureau_controle', 'bureau_etudes']);
        $chefBc = $this->chefs['bureau_controle'];

        $this->actingAs($chefBc)->post(route('cheflot.documents.recus.decision', $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chefBc))->assertSessionHasNoErrors();

        // BC a visé : 1 visé, 0 en attente. BE n'a pas encore visé : 1 en attente, 0 visé.
        $this->actingAs($chefBc)->get(route('cheflot.dashboard'))->assertViewHas('documentsValides', 1)->assertViewHas('documentsEnAttente', 0);
        $this->actingAs($this->chefs['bureau_etudes'])->get(route('bureau_etudes.chef.dashboard'))->assertViewHas('documentsValides', 0)->assertViewHas('documentsEnAttente', 1);
    }

    public function test_un_document_vise_ne_peut_plus_etre_supprime(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle']);
        $chef = $this->chefs['entreprise'];
        $this->assertTrue($dossier->estSupprimable());

        $chefBc = $this->chefs['bureau_controle'];
        $this->actingAs($chefBc)->post(route('cheflot.documents.recus.decision', $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chefBc));

        $this->assertFalse($dossier->fresh()->estSupprimable());
        $this->actingAs($chef)->delete(route('entreprise.dossiers.destroy', $dossier->id))->assertForbidden();
        $this->assertNotNull(Dossier::find($dossier->id));
        $this->actingAs($chef)->get(route('entreprise.documents'))->assertOk()->assertDontSee('title="Supprimer"', false);
    }

    public function test_les_visas_d_une_version_ne_reprennent_pas_ceux_d_une_autre_version(): void
    {
        [$dossier, $v1] = $this->dossierEnCircuit(['bureau_controle']);
        $dossier->update(['statut' => 'valide']);
        $v1->update(['statut' => 'a_corriger']);
        CircuitVisa::where('document_version_id', $v1->id)->delete(); // ancien document, sans circuit

        $v2 = DocumentVersion::create([
            'dossier_id' => $dossier->id, 'numero_version' => 2, 'fichier_nom' => 'plan2.pdf', 'fichier_chemin' => 'documents/plan2.pdf',
            'importe_par' => $this->chefs['entreprise']->id, 'date_import' => now(), 'statut' => 'valide',
        ]);

        $this->assertSame('a_corriger', $v1->fresh()->visas_resume[0]['statut']);
        $this->assertSame('valide', $v2->fresh()->visas_resume[0]['statut']);
    }

    public function test_les_tableaux_de_bord_maitre_d_ouvrage_et_admin_s_affichent(): void
    {
        [$dossier] = $this->dossierEnCircuit(['bureau_controle']);
        $mo = Structure::create(['nom' => 'MO', 'type' => 'maitre_ouvrage']);
        $chefMo = $this->utilisateur(User::ROLE_MAITRE_OUVRAGE_CHEF, 'responsable_organisme', $mo);
        $this->actingAs($chefMo)->get(route('maitre_ouvrage.chef.dashboard'))->assertOk();

        $admin = $this->utilisateur(User::ROLE_ADMIN, 'responsable_organisme', $mo);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_la_fiche_d_un_evenement_s_affiche_pour_l_organisateur_et_un_invite(): void
    {
        // La migration des structures visées d'un événement ne se rejoue pas sur SQLite
        if (!Schema::hasTable('event_structure')) {
            Schema::create('event_structure', function (Blueprint $t) {
                $t->foreignId('event_id');
                $t->foreignId('structure_id');
                $t->timestamps();
            });
        }

        $organisateur = $this->chefs['bureau_controle'];
        $invite = $this->chefs['maitre_oeuvre'];
        $event = \App\Models\Event::create(['titre' => 'Réunion de coordination', 'date_debut' => now()->addDay(), 'lieu' => 'SIRAT', 'createur_id' => $organisateur->id]);
        $event->structures()->sync([$this->structures['maitre_oeuvre']->id]);
        $event->participants()->sync([$invite->id => ['statut' => 'en_attente']]);

        $this->actingAs($organisateur)->get(route('events.show', $event))->assertOk()->assertSee('Réunion de coordination')->assertSee('Participants')->assertSee('Modifier');
        $this->actingAs($invite)->get(route('events.show', $event))->assertOk()->assertSee('Je serai présent(e)')->assertDontSee('Supprimer');
    }

    public function test_les_pages_des_demandes_d_information_s_affichent(): void
    {
        $chef = $this->chefs['bureau_controle'];
        $this->actingAs($chef)->get(route('rfi.index'))->assertOk()->assertSee('Aucune demande');
        $this->actingAs($chef)->get(route('rfi.create'))->assertOk()->assertSee('Comment ça marche');
    }

    public function test_modifier_la_decision_remet_le_document_a_corriger_et_journalise(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle', 'bureau_etudes']);
        $chefBc = $this->chefs['bureau_controle'];

        $this->actingAs($chefBc)
            ->post(route('cheflot.documents.recus.decision', $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chefBc))
            ->assertSessionHasNoErrors();
        $this->assertSame('valide', $dossier->fresh()->statut);

        // Un autre chef (BE), qui n'a pas visé, ne peut pas modifier la décision du BC.
        $this->actingAs($this->chefs['bureau_etudes'])
            ->post(route('dossiers.decision.modifier', $version), ['motif' => 'Je ne devrais pas pouvoir'])
            ->assertForbidden();

        // Le motif est obligatoire.
        $this->actingAs($chefBc)->post(route('dossiers.decision.modifier', $version), ['motif' => ''])->assertSessionHasErrors('motif');

        $this->actingAs($chefBc)
            ->post(route('dossiers.decision.modifier', $version), ['motif' => 'Plan de ferraillage à revoir'])
            ->assertSessionHasNoErrors();

        $this->assertSame('a_corriger', $dossier->fresh()->statut);
        $this->assertSame('a_corriger', $version->fresh()->statut);
        $this->assertSame('a_corriger', CircuitVisa::where('document_version_id', $version->id)->where('structure_type', 'bureau_controle')->value('statut'));

        $actions = $dossier->evenements()->where('structure_type', 'bureau_controle')->pluck('action')->all();
        $this->assertSame(['envoi', 'visa', 'decision_modifiee'], $actions);
        $this->assertSame('Plan de ferraillage à revoir', $dossier->evenements()->where('action', 'decision_modifiee')->value('motif'));

        // La page du dossier montre l'historique, chez l'émetteur comme chez la structure.
        foreach ([$this->chefs['entreprise'], $chefBc] as $acteur) {
            $this->actingAs($acteur)->get(route('dossiers.show', $dossier))->assertOk()
                ->assertSee('Historique du circuit')->assertSee('Décision modifiée')->assertSee('Plan de ferraillage à revoir');
        }
        // Le bouton n'est plus proposé une fois la décision modifiée.
        $this->actingAs($chefBc)->get(route('cheflot.documents.recus.show', $dossier->id))->assertOk()->assertDontSee('Modifier la décision');
    }

    public function test_le_bouton_modifier_la_decision_est_propose_au_chef_qui_a_vise(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle']);
        $chefBc = $this->chefs['bureau_controle'];

        $this->actingAs($chefBc)->get(route('cheflot.documents.recus.show', $dossier->id))->assertOk()->assertDontSee('Modifier la décision');

        $this->actingAs($chefBc)
            ->post(route('cheflot.documents.recus.decision', $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chefBc));

        $this->actingAs($chefBc)->get(route('cheflot.documents.recus.show', $dossier->id))->assertOk()
            ->assertSee('Modifier la décision')->assertSee('Retour à la liste');
    }

    public function test_rendre_applicable_reserve_au_chef_entreprise_une_fois_tous_les_visas_obtenus(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle', 'bureau_etudes']);
        $chefEntreprise = $this->chefs['entreprise'];

        $viser = function (string $type, string $route) use ($dossier) {
            $chef = $this->chefs[$type];
            $this->actingAs($chef)->post(route($route, $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chef))->assertSessionHasNoErrors();
        };

        $viser('bureau_controle', 'cheflot.documents.recus.decision');
        // Un seul visa sur deux : pas encore applicable.
        $this->actingAs($chefEntreprise)->post(route('dossiers.applicable', $dossier))->assertForbidden();

        $viser('bureau_etudes', 'bureau_etudes.chef.documents.recus.decision');

        // Ni un collaborateur, ni une autre structure ne peuvent le faire.
        $this->actingAs($this->collabs['entreprise'])->post(route('dossiers.applicable', $dossier))->assertForbidden();
        $this->actingAs($this->chefs['bureau_controle'])->post(route('dossiers.applicable', $dossier))->assertForbidden();

        $this->actingAs($chefEntreprise)->post(route('dossiers.applicable', $dossier))->assertSessionHasNoErrors();
        $this->assertTrue($dossier->fresh()->estApplicable());
        $this->assertSame('Applicable', $dossier->fresh()->statut_label);

        // La liste « Documents applicables » est visible de toutes les structures.
        foreach (['bureau_controle', 'bureau_etudes', 'maitre_oeuvre', 'entreprise'] as $type) {
            $this->actingAs($this->chefs[$type])->get(route('documents.applicables'))->assertOk()->assertSee($dossier->identifiant);
            $this->actingAs($this->collabs[$type])->get(route('documents.applicables'))->assertOk()->assertSee($dossier->identifiant);
        }
        // … et le lien est dans leur menu.
        $this->actingAs($this->collabs['maitre_oeuvre'])->get(route('maitre_oeuvre.collaborateur.dashboard'))->assertSee('Documents applicables');

        // Une structure revient sur son visa : le statut applicable disparaît.
        $this->actingAs($this->chefs['bureau_etudes'])
            ->post(route('dossiers.decision.modifier', $version), ['motif' => 'Erreur de calcul trouvée'])
            ->assertSessionHasNoErrors();
        $this->assertFalse($dossier->fresh()->estApplicable());
        $this->assertNull($dossier->fresh()->applicable_le);
        $this->actingAs($this->chefs['bureau_controle'])->get(route('documents.applicables'))->assertOk()->assertDontSee($dossier->identifiant);
    }

    public function test_le_cachet_de_chaque_structure_est_pose_a_son_emplacement_sur_le_pdf(): void
    {
        $this->structures['maitre_ouvrage'] = Structure::create(['nom' => 'MO', 'type' => 'maitre_ouvrage']);
        $this->chefs['maitre_ouvrage'] = $this->utilisateur(User::ROLE_MAITRE_OUVRAGE_CHEF, 'responsable_organisme', $this->structures['maitre_ouvrage']);

        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle', 'bureau_etudes', 'maitre_oeuvre', 'maitre_ouvrage']);

        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 14);
        $pdf->Cell(0, 10, 'Document de test');
        Storage::disk('public')->put('documents/plan.pdf', $pdf->Output('S'));

        foreach (['bureau_controle', 'bureau_etudes', 'maitre_oeuvre', 'maitre_ouvrage'] as $type) {
            $chef = $this->chefs[$type];
            $this->actingAs($chef);
            app(\App\Services\VisaService::class)->enregistrer(
                \Illuminate\Http\Request::create('/', 'POST', $this->pieceVisa($chef)), $chef, $version, 'validation', null, false
            );
        }

        $this->assertTrue(app(\App\Services\DocumentStampingService::class)->tamponner($version->fresh()));
        $chemin = $version->fresh()->fichier_valide_chemin;
        $this->assertNotEmpty($chemin);
        $contenu = Storage::disk('public')->get($chemin);
        $this->assertStringStartsWith('%PDF', $contenu);

        if ($sortie = getenv('STAMP_OUT')) {
            file_put_contents($sortie, $contenu);
        }
    }

    public function test_les_structures_visantes_envoient_leurs_documents_comme_l_entreprise(): void
    {
        $this->structures['maitre_ouvrage'] = Structure::create(['nom' => 'MO', 'type' => 'maitre_ouvrage']);
        $this->chefs['maitre_ouvrage'] = $this->utilisateur(User::ROLE_MAITRE_OUVRAGE_CHEF, 'responsable_organisme', $this->structures['maitre_ouvrage']);
        $this->collabs['maitre_ouvrage'] = $this->utilisateur(User::ROLE_MAITRE_OUVRAGE_COLLABORATEUR, 'collaborateur', $this->structures['maitre_ouvrage']);

        $type = DocumentType::create(['code' => 'NCA', 'nom' => 'Note', 'categorie' => 'etude_execution', 'mode_traitement' => 'validation', 'actif' => true]);

        $envois = [
            'bureau_controle' => ['bureau_controle.envoi.', 'cheflot.dashboard'],
            'bureau_etudes' => ['bureau_etudes.envoi.', 'bureau_etudes.chef.dashboard'],
            'maitre_oeuvre' => ['maitre_oeuvre.envoi.', 'maitre_oeuvre.chef.dashboard'],
            'maitre_ouvrage' => ['maitre_ouvrage.envoi.', 'maitre_ouvrage.chef.dashboard'],
        ];

        foreach ($envois as $structure => [$prefixe, $dashboard]) {
            $chef = $this->chefs[$structure];
            $autres = array_values(array_diff(array_keys($envois), [$structure]));

            // La page « Mes documents » s'affiche dans l'espace de la structure ; elle ne propose pas son propre visa.
            $page = $this->actingAs($chef)->get(route($prefixe . 'documents'));
            $page->assertOk();
            $this->actingAs($chef)->get(route($dashboard))->assertOk()->assertSee('Mes documents');

            $donnees = [
                'fichiers' => [UploadedFile::fake()->create('note.pdf', 100, 'application/pdf')],
                'document_type_id' => [$type->id], 'titre' => ['Note ' . $structure], 'mode' => ['validation'],
                'circuit' => [[$autres[0], $autres[1]]],
            ] + $this->pieceVisa($chef);

            $this->actingAs($chef)->post(route($prefixe . 'transmettre'), $donnees)->assertSessionHasNoErrors();

            $dossier = Dossier::where('titre', 'Note ' . $structure)->firstOrFail();
            $this->assertSame($this->structures[$structure]->id, $dossier->structure_emettrice_id);
            $this->assertEqualsCanonicalizing([$autres[0], $autres[1]], $dossier->circuitVisas->pluck('structure_type')->all());

            // Un chef d'une autre structure reçoit le document dans ses « Documents reçus ».
            $this->actingAs($this->chefs[$autres[0]])->get(route(\App\Support\Espaces::nomRoute('cheflot.documents.recus', $this->chefs[$autres[0]])))
                ->assertOk()->assertSee($dossier->identifiant);
        }

        // Le collaborateur d'un BE soumet à son chef, qui le retrouve dans « À transférer ».
        $this->actingAs($this->collabs['bureau_etudes'])->post(route('bureau_etudes.envoi.transmettre'), [
            'fichiers' => [UploadedFile::fake()->create('n.pdf', 50, 'application/pdf')],
            'document_type_id' => [$type->id], 'titre' => ['Note collab'], 'mode' => ['validation'], 'circuit' => [['maitre_oeuvre']],
        ])->assertSessionHasNoErrors();
        $this->actingAs($this->chefs['bureau_etudes'])->get(route('bureau_etudes.envoi.documents.a-transferer'))->assertOk()->assertSee('Note collab');

        // Une autre espace ne peut pas utiliser ces routes.
        $this->actingAs($this->chefs['entreprise'])->get(route('bureau_etudes.envoi.documents'))->assertRedirect();
    }

    public function test_l_emetteur_ajoute_une_structure_oubliee_sans_casser_le_circuit_en_cours(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle']);
        $chefEntreprise = $this->chefs['entreprise'];
        $chefBc = $this->chefs['bureau_controle'];

        // Le BC vise : le document est validé, le circuit n'attend plus rien (applicable possible).
        $this->actingAs($chefBc)->post(route('cheflot.documents.recus.decision', $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chefBc))->assertSessionHasNoErrors();
        $this->actingAs($chefEntreprise)->post(route('dossiers.applicable', $dossier))->assertSessionHasNoErrors();
        $this->assertTrue($dossier->fresh()->estApplicable());

        // L'icône « Partager » est proposée à l'émetteur, pas à une autre structure ; le BC ne peut pas se choisir lui-même.
        $this->actingAs($chefEntreprise)->get(route('entreprise.documents'))->assertOk()->assertSee('fa-user-plus');
        $this->actingAs($chefBc)->post(route('dossiers.partager', $dossier), ['structures' => ['bureau_etudes']])->assertForbidden();
        $this->actingAs($chefEntreprise)->post(route('dossiers.partager', $dossier), ['structures' => []])->assertSessionHasErrors('structures');

        $this->actingAs($chefEntreprise)->post(route('dossiers.partager', $dossier), ['structures' => ['bureau_etudes', 'bureau_controle']])->assertSessionHasNoErrors();

        // BE ajouté en attente ; BC (déjà dans le circuit) intact ; le document n'est plus applicable.
        $statuts = CircuitVisa::where('document_version_id', $version->id)->pluck('statut', 'structure_type')->all();
        $this->assertSame(['bureau_controle' => 'valide', 'bureau_etudes' => 'en_attente'], $statuts);
        $this->assertFalse($dossier->fresh()->estApplicable());
        $this->assertSame('valide', $dossier->fresh()->statut);
        $this->assertSame('BC,BE', $dossier->fresh()->structure_cible);

        // Le chef du BE reçoit le document, vise à son niveau ; tout est dans l'historique.
        $chefBe = $this->chefs['bureau_etudes'];
        $this->actingAs($chefBe)->get(route('bureau_etudes.chef.documents.recus'))->assertOk()->assertSee($dossier->identifiant);
        $this->actingAs($chefBe)->post(route('bureau_etudes.chef.documents.recus.decision', $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chefBe))->assertSessionHasNoErrors();
        $this->assertTrue($dossier->fresh()->visasComplets());
        $this->actingAs($chefEntreprise)->get(route('dossiers.show', $dossier))->assertOk()->assertSee('Structure ajoutée au circuit');

        // Plus rien à ajouter une fois que BC et BE y sont et qu'il reste MOE et MO : MOE reste proposable.
        $this->actingAs($chefEntreprise)->post(route('dossiers.partager', $dossier), ['structures' => ['maitre_oeuvre']])->assertSessionHasNoErrors();
        $this->assertSame(3, CircuitVisa::where('document_version_id', $version->id)->count());
    }

    public function test_un_destinataire_peut_retransmettre_le_document_a_son_tour(): void
    {
        [$dossier] = $this->dossierEnCircuit(['bureau_controle']);
        $chefBc = $this->chefs['bureau_controle'];
        $collabBe = $this->collabs['bureau_etudes'];

        // Le collaborateur du BE n'a pas le document ; le chef du BC (destinataire) le lui transmet.
        $this->actingAs($collabBe)->get(route('dossiers.retransmettre.form', $dossier))->assertForbidden();
        $this->actingAs($collabBe)->get(route('dossiers.show', $dossier))->assertForbidden();

        $this->actingAs($chefBc)->get(route('dossiers.retransmettre.form', $dossier))->assertOk();
        $this->actingAs($chefBc)->post(route('dossiers.retransmettre', $dossier), [])->assertSessionHasErrors('destinataires');
        $this->actingAs($chefBc)->post(route('dossiers.retransmettre', $dossier), ['users' => [$collabBe->id]])->assertSessionHasNoErrors();

        $this->actingAs($collabBe)->get(route('dossiers.show', $dossier))->assertOk()->assertSee('Transmettre');

        // … qui peut le transmettre à son tour, mais n'a aucun droit de visa dessus.
        $this->actingAs($collabBe)->post(route('dossiers.retransmettre', $dossier), ['users' => [$this->collabs['maitre_oeuvre']->id]])->assertSessionHasNoErrors();
        $this->actingAs($this->collabs['maitre_oeuvre'])->get(route('dossiers.show', $dossier))->assertOk();
        $this->actingAs($collabBe)->post(route('bureau_etudes.chef.documents.recus.decision', $dossier->id), ['decision' => 'a_corriger'])->assertRedirect();
        $this->assertSame('en_cours', $dossier->fresh()->statut);
    }

    public function test_le_maitre_d_ouvrage_est_le_quatrieme_niveau_du_circuit(): void
    {
        $this->structures['maitre_ouvrage'] = Structure::create(['nom' => 'MO', 'type' => 'maitre_ouvrage']);
        $chefMo = $this->chefs['maitre_ouvrage'] = $this->utilisateur(User::ROLE_MAITRE_OUVRAGE_CHEF, 'responsable_organisme', $this->structures['maitre_ouvrage']);
        $collabMo = $this->utilisateur(User::ROLE_MAITRE_OUVRAGE_COLLABORATEUR, 'collaborateur', $this->structures['maitre_ouvrage']);

        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle', 'bureau_etudes', 'maitre_oeuvre', 'maitre_ouvrage']);
        $this->assertSame(4, CircuitVisa::where('document_version_id', $version->id)->count());
        $this->assertSame('BC,BE,MOE,MO', $dossier->structure_cible);

        // Le MO reçoit le document, l'affecte à son collaborateur, qui analyse ; le chef vise.
        $this->actingAs($chefMo)->get(route('maitre_ouvrage.chef.documents.recus'))->assertOk()->assertSee($dossier->identifiant);
        $this->actingAs($chefMo)->get(route('maitre_ouvrage.chef.dashboard'))->assertOk()->assertSee('Documents reçus');
        $this->actingAs($chefMo)->get(route('maitre_ouvrage.chef.dossiers.affecter', $version))->assertOk();
        $this->actingAs($collabMo)->get(route('maitre_ouvrage.collaborateur.dashboard'))->assertOk()->assertSee('Mes affectations');
        $this->actingAs($collabMo)->get(route('maitre_ouvrage.collaborateur.affectations.index'))->assertOk();

        $this->actingAs($chefMo)
            ->post(route('maitre_ouvrage.chef.documents.recus.decision', $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chefMo))
            ->assertSessionHasNoErrors();

        $this->assertSame('valide', CircuitVisa::where('document_version_id', $version->id)->where('structure_type', 'maitre_ouvrage')->value('statut'));
        $resume = collect($dossier->fresh()->load('circuitVisas')->visas_resume)->pluck('statut', 'sigle')->all();
        $this->assertSame('valide', $resume['MO']);
        $this->assertSame('en_attente', $resume['BC']);

        // Le MO peut revenir sur son visa.
        $this->actingAs($chefMo)->post(route('dossiers.decision.modifier', $version), ['motif' => 'Réserve du maître d\'ouvrage'])->assertSessionHasNoErrors();
        $this->assertSame('a_corriger', $dossier->fresh()->statut);
    }

    public function test_un_document_valide_n_est_archivable_qu_une_fois_tous_les_visas_obtenus(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle', 'bureau_etudes']);
        $chefEntreprise = $this->chefs['entreprise'];
        $chefBc = $this->chefs['bureau_controle'];

        $this->actingAs($chefBc)->post(route('cheflot.documents.recus.decision', $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chefBc))->assertSessionHasNoErrors();
        $this->assertSame('valide', $dossier->fresh()->statut);

        // Validé par le BC seulement : le BE n'a pas visé, donc ni bouton ni archivage possible.
        $this->assertFalse($dossier->fresh()->archivable());
        $this->actingAs($chefEntreprise)->get(route('entreprise.documents'))->assertOk()->assertDontSee('Archiver');
        $this->actingAs($chefEntreprise)->post(route('dossiers.archiver', $dossier))->assertRedirect()->assertSessionHas('error');
        $this->assertSame('valide', $dossier->fresh()->statut);

        $chefBe = $this->chefs['bureau_etudes'];
        $this->actingAs($chefBe)->post(route('bureau_etudes.chef.documents.recus.decision', $dossier->id), ['decision' => 'valide'] + $this->pieceVisa($chefBe))->assertSessionHasNoErrors();

        $this->assertTrue($dossier->fresh()->archivable());
        $this->actingAs($chefEntreprise)->post(route('dossiers.archiver', $dossier))->assertRedirect(route('archives.index'));
        $this->assertSame('archive', $dossier->fresh()->statut);
    }

    public function test_partager_une_archive_la_rend_visible_aux_destinataires_et_prevenus_par_mail_au_gabarit_commun(): void
    {
        [$dossier] = $this->dossierEnCircuit(['bureau_controle']);
        $dossier->update(['statut' => 'valide']);
        $dossier->circuitVisas()->update(['statut' => 'valide']);
        $chefEntreprise = $this->chefs['entreprise'];

        $this->actingAs($chefEntreprise)->post(route('dossiers.archiver', $dossier))->assertRedirect(route('archives.index'));
        $this->assertSame('archive', $dossier->fresh()->statut);

        $collabBe = $this->collabs['bureau_etudes'];
        $this->actingAs($collabBe)->get(route('archives.index'))->assertOk()->assertDontSee($dossier->identifiant);

        // Bouton « Partager » sur la page des archives, puis partage nominatif avec un collaborateur et avec la structure MOE.
        $this->actingAs($chefEntreprise)->get(route('archives.index'))->assertOk()->assertSee('Partager')->assertDontSee('classés par structure émettrice');
        $this->actingAs($chefEntreprise)->post(route('dossiers.retransmettre', $dossier), [])->assertSessionHasErrors('destinataires');
        $this->actingAs($chefEntreprise)->post(route('dossiers.retransmettre', $dossier), [
            'users' => [$collabBe->id], 'structures' => [$this->structures['maitre_oeuvre']->id],
        ])->assertSessionHasNoErrors();

        // Nommément : visible même par un collaborateur. Par structure : le chef seulement.
        $this->actingAs($collabBe)->get(route('archives.index'))->assertOk()->assertSee($dossier->identifiant);
        $this->actingAs($this->chefs['maitre_oeuvre'])->get(route('archives.index'))->assertOk()->assertSee($dossier->identifiant);
        $this->actingAs($this->collabs['maitre_oeuvre'])->get(route('archives.index'))->assertOk()->assertDontSee($dossier->identifiant);

        // Le destinataire peut le partager à son tour.
        $this->actingAs($collabBe)->post(route('dossiers.retransmettre', $dossier), ['users' => [$this->collabs['bureau_controle']->id]])->assertSessionHasNoErrors();
        $this->actingAs($this->collabs['bureau_controle'])->get(route('archives.index'))->assertOk()->assertSee($dossier->identifiant);
    }

    public function test_les_mails_ont_le_meme_gabarit_et_aucun_emoji(): void
    {
        [$dossier, $version] = $this->dossierEnCircuit(['bureau_controle']);
        $destinataire = $this->chefs['bureau_controle'];

        foreach ([
            new \App\Notifications\DocumentArchive($dossier),
            new \App\Notifications\DocumentApplicable($dossier),
            new \App\Notifications\DocumentValide($dossier),
            new \App\Notifications\CorrectionDemandee($dossier, 'À reprendre'),
            new \App\Notifications\DocumentRecu($dossier),
        ] as $notification) {
            $mail = $notification->toMail($destinataire);
            $html = (string) $mail->render();
            $this->assertStringContainsString('class="container"', $html, get_class($notification) . ' n\'utilise pas le gabarit commun');
            $this->assertStringContainsString('FawazBTP', $html);
            $this->assertDoesNotMatchRegularExpression('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $mail->subject . $html);
        }

        // Aucun emoji dans le code des mails.
        foreach (array_merge(glob(app_path('Mail/*.php')), glob(app_path('Notifications/*.php')), glob(resource_path('views/emails/*.php')), glob(resource_path('views/emails/*/*.php'))) as $fichier) {
            $this->assertDoesNotMatchRegularExpression('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B50}]/u', file_get_contents($fichier), basename($fichier));
        }
    }

    public function test_la_page_des_notifications_et_l_etat_vide(): void
    {
        $chef = $this->chefs['bureau_controle'];
        $this->actingAs($chef)->get(route('notifications.index'))->assertOk()->assertSee('Aucune notification pour l\'instant', false)->assertSee('nt-vide');

        \App\Notifications\Alerte::envoyer($chef, 'Nouveau document à viser : Plan', '/dossiers/1');
        $this->actingAs($chef)->get(route('notifications.index'))->assertOk()->assertSee('nt-carte')->assertSee('Nouveau document à viser')->assertSee('Plan');
    }

    public function test_on_partage_a_tout_moment_et_les_documents_partages_ont_leur_propre_titre(): void
    {
        $chef = $this->chefs['entreprise'];
        $collabBe = $this->collabs['bureau_etudes'];

        // Document encore « Soumis » (ni validé ni archivé) : le partage informatif est déjà possible.
        [$dossier] = $this->dossierEnCircuit(['bureau_controle']);
        $this->assertNotContains($dossier->statut, ['valide', 'archive']);

        $this->actingAs($chef)->get(route('entreprise.documents'))->assertOk()->assertSee('btn-partager')->assertSee('partagerModal');
        $this->actingAs($chef)->post(route('dossiers.retransmettre', $dossier), ['users' => [$collabBe->id]])->assertSessionHasNoErrors();

        // Chez le destinataire : son propre espace distingue « Partagés avec moi » de ses documents.
        $this->actingAs($collabBe)->get(route('dossiers.index'))->assertOk()
            ->assertSee('Partagés avec moi')->assertSee($dossier->identifiant)->assertSee('btn-partager');

        // Chez l'émetteur : pas de titre « Partagés avec moi » pour ses propres documents.
        $this->actingAs($chef)->get(route('entreprise.dossiers'))->assertOk()->assertDontSee('Partagés avec moi')->assertSee($dossier->identifiant);

        // Il archive (après validation de tous les visas) : il le retrouve, et celui à qui il l'avait partagé aussi, sous leurs titres.
        $dossier->update(['statut' => 'valide']);
        $dossier->circuitVisas()->update(['statut' => 'valide']);
        $this->actingAs($chef)->post(route('dossiers.archiver', $dossier))->assertRedirect(route('archives.index'));

        $this->actingAs($chef)->get(route('archives.index'))->assertOk()->assertSee($dossier->identifiant)->assertDontSee('Partagés avec moi');
        $this->actingAs($collabBe)->get(route('archives.index'))->assertOk()->assertSee('Partagés avec moi')->assertSee($dossier->identifiant)->assertSee('fa-eye');
    }

    public function test_le_lien_dossiers_est_le_meme_partout_et_sans_bouton_d_import(): void
    {
        foreach ([
            ['bureau_controle', 'controleur.dashboard', 'cheflot.dashboard', 'bureau_controle.envoi.dossiers'],
            ['bureau_etudes', 'bureau_etudes.collaborateur.dashboard', 'bureau_etudes.chef.dashboard', 'bureau_etudes.envoi.dossiers'],
            ['maitre_oeuvre', 'maitre_oeuvre.collaborateur.dashboard', 'maitre_oeuvre.chef.dashboard', 'maitre_oeuvre.envoi.dossiers'],
        ] as [$type, $dashCollab, $dashChef, $routeDossiers]) {
            foreach ([$this->collabs[$type], $this->chefs[$type]] as $user) {
                $dash = $user->peutViser() ? $dashChef : $dashCollab;
                $this->actingAs($user)->get(route($dash))->assertOk()
                    ->assertSee(route($routeDossiers))->assertDontSee('Tous les dossiers');
                $this->actingAs($user)->get(route($routeDossiers))->assertOk()->assertSee('Mes dossiers');
            }
        }

        $this->actingAs($this->collabs['bureau_controle'])->get(route('dossiers.index'))->assertOk()->assertDontSee('Importer un document');
    }

    public function test_les_formulaires_utilisateur_de_l_admin_remplissent_la_largeur_et_regroupent_affectation_et_statut(): void
    {
        $admin = $this->utilisateur(User::ROLE_ADMIN, 'responsable_organisme', $this->structures['entreprise']);
        $cible = $this->chefs['maitre_oeuvre'];

        $this->actingAs($admin)->get(route('admin.users.create'))->assertOk()->assertSee('form-row fluide', false);
        $edition = $this->actingAs($admin)->get(route('admin.users.edit', $cible))->assertOk()->assertSee('form-row fluide', false);

        // « Affectation » et « Statut du compte » sont deux blocs côte à côte dans un même conteneur à deux colonnes.
        $edition->assertSeeInOrder(['class="duo"', 'Affectation', 'name="lot_id"', 'Statut du compte', 'name="statut"'], false);
    }
}
