<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');
            $table->enum('categorie', [
                'etude_execution',
                'gestion_projet',
                'assurance_qualite',
                'environnemental_social',
                'bc_document',
            ]);
            $table->enum('mode_traitement', ['simple', 'validation'])->default('validation');
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index('categorie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
