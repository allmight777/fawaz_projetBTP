<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Journal du circuit de visa : tout ce qui est fait ou demandé sur un document, par qui et quand.
        Schema::create('circuit_evenements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('structure_type')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->text('motif')->nullable();
            $table->timestamps();
            $table->index(['dossier_id', 'created_at']);
        });

        // « Applicable » : décision du chef d'entreprise une fois tous les visas obtenus.
        Schema::table('dossiers', function (Blueprint $table) {
            $table->timestamp('applicable_le')->nullable();
            $table->foreignId('applicable_par')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('applicable_par');
            $table->dropColumn('applicable_le');
        });
        Schema::dropIfExists('circuit_evenements');
    }
};
