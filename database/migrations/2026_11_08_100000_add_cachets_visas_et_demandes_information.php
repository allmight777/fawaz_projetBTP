<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cachet_chemin')->nullable();
        });

        Schema::table('document_versions', function (Blueprint $table) {
            // Copie du document avec page de visas (cachets + signatures), servie avant la validation finale
            $table->string('fichier_vise_chemin')->nullable();
        });

        Schema::create('document_visas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20); // envoi | validation
            $table->string('fonction')->nullable();
            $table->string('destinataire')->nullable();
            $table->string('cachet_chemin');
            $table->string('signature_chemin');
            $table->timestamp('signe_le');
            $table->timestamps();
        });

        Schema::create('demandes_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demandeur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('destinataire_id')->constrained('users')->cascadeOnDelete();
            $table->string('portee', 10); // interne | externe
            $table->string('objet');
            $table->text('message');
            $table->string('statut', 20)->default('en_attente'); // en_attente | accordee | refusee | cloturee
            $table->foreignId('dossier_id')->nullable()->constrained()->nullOnDelete(); // document partagé
            $table->text('reponse')->nullable();
            $table->timestamp('repondu_le')->nullable();
            $table->timestamp('cloture_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_information');
        Schema::dropIfExists('document_visas');
        Schema::table('document_versions', fn (Blueprint $t) => $t->dropColumn('fichier_vise_chemin'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('cachet_chemin'));
    }
};
