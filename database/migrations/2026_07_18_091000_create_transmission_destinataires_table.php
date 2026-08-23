<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nom de table raccourci (limite MySQL de 64 caractères pour les noms
        // de contraintes générés automatiquement par Laravel).
        Schema::create('transmission_destinataires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_transmission_id')->constrained('document_transmissions')->cascadeOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('consulte_le')->nullable();
            $table->timestamps();

            $table->index('document_transmission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transmission_destinataires');
    }
};
