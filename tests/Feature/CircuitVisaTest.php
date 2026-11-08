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

    public function test_le_document_n_est_valide_que_lorsque_toutes_les_structures_ont_vise(): void
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

            $this->assertNotSame('valide', $dossier->fresh()->statut, "Validé trop tôt après le visa $type");
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
}
