<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();
            $table->string('identifiant')->unique();
            $table->foreignId('document_type_id')->constrained('document_types')->restrictOnDelete();
            $table->foreignId('structure_emettrice_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained('lots')->nullOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('mode_traitement', ['simple', 'validation']);
            $table->enum('statut', ['transmis', 'soumis', 'en_analyse', 'valide', 'a_corriger', 'archive'])->default('soumis');
            $table->unsignedInteger('version_courante')->default(1);
            $table->foreignId('cree_par')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('identifiant');
            $table->index('statut');
            $table->index('document_type_id');
            $table->index('structure_emettrice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};
