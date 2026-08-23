<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_assignment_id')->constrained('document_assignments')->cascadeOnDelete();
            $table->foreignId('auteur_id')->constrained('users')->restrictOnDelete();
            $table->enum('type', ['observation', 'non_conformite'])->default('observation');
            $table->text('contenu');
            $table->boolean('retenue')->default(false);
            $table->timestamps();

            $table->index('document_assignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_observations');
    }
};
