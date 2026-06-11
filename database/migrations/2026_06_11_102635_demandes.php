<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_demande')->unique();
            $table->timestamp('date_soumission')->useCurrent();
            $table->foreignId('soumis_par')->constrained('users')->onDelete('cascade');
            $table->string('entreprise');
            $table->foreignId('lot_id')->nullable()->constrained('lots')->onDelete('set null');
            $table->enum('type_document', ['Plan', 'Rapport', 'Contrat', 'Devis', 'Facture', 'Autre'])->default('Plan');
            $table->string('titre_document');
            $table->text('description')->nullable();
            $table->string('version')->default('V1');
            $table->integer('version_number')->default(1);
            $table->foreignId('document_id')->nullable()->constrained('documents')->onDelete('set null');
            $table->string('fichier_url')->nullable();
            $table->string('fichier_chemin')->nullable(); // Pour stocker le chemin du fichier uploadé
            $table->string('fichier_nom')->nullable();    // Nom original du fichier
            $table->enum('statut', ['EN ATTENTE', 'EN CONTROLE', 'VALIDE', 'REJETE', 'ARCHIVE'])->default('EN ATTENTE');
            $table->foreignId('controleur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('priorite', ['Basse', 'Moyenne', 'Haute', 'Urgente'])->default('Moyenne');
            $table->timestamp('echeance_controle')->nullable();
            $table->enum('statut_delai', ['A JOUR', 'EN RETARD'])->default('A JOUR');
            $table->timestamp('derniere_maj')->nullable();
            $table->enum('decision_controleur', ['VALIDE', 'REJETE', 'EN ATTENTE'])->nullable();
            $table->timestamp('date_decision')->nullable();
            $table->text('commentaire_controleur')->nullable();
            $table->foreignId('parent_demande_id')->nullable()->constrained('demandes')->onDelete('cascade');
            $table->boolean('est_actif')->default(true);
            $table->timestamps();

            $table->index('numero_demande');
            $table->index('soumis_par');
            $table->index('lot_id');
            $table->index('statut');
            $table->index('controleur_id');
            $table->index('priorite');
        });

        DB::statement('
            CREATE TRIGGER update_demandes_derniere_maj
            BEFORE UPDATE ON demandes
            FOR EACH ROW
            SET NEW.derniere_maj = NOW()
        ');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS update_demandes_derniere_maj');
        Schema::dropIfExists('demandes');
    }
};
