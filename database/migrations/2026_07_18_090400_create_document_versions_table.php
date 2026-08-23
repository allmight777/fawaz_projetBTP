<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->cascadeOnDelete();
            $table->unsignedInteger('numero_version');
            $table->string('fichier_nom')->nullable();
            $table->string('fichier_chemin')->nullable();
            $table->string('fichier_url')->nullable();
            $table->foreignId('importe_par')->constrained('users')->restrictOnDelete();
            $table->timestamp('date_import')->useCurrent();
            $table->enum('statut', ['en_attente_checklist', 'soumis', 'en_analyse', 'valide', 'a_corriger'])->default('en_attente_checklist');
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->unique(['dossier_id', 'numero_version']);
            $table->index('dossier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
