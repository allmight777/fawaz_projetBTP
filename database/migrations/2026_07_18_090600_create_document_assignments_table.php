<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_version_id')->constrained('document_versions')->cascadeOnDelete();
            $table->foreignId('controleur_id')->constrained('users')->cascadeOnDelete();
            $table->string('specialite')->nullable();
            $table->foreignId('affecte_par')->constrained('users')->restrictOnDelete();
            $table->timestamp('date_affectation')->useCurrent();
            $table->enum('statut', ['en_attente', 'en_cours', 'termine'])->default('en_attente');
            $table->timestamps();

            $table->index('document_version_id');
            $table->index('controleur_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_assignments');
    }
};
