<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_version_id')->constrained('document_versions')->cascadeOnDelete();
            $table->enum('decision', ['valide', 'a_corriger']);
            $table->foreignId('validateur_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('date_decision')->useCurrent();
            $table->text('commentaires')->nullable();
            $table->timestamps();

            $table->index('document_version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_decisions');
    }
};
