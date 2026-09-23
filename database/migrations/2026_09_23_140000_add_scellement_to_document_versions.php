<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            $table->string('fichier_valide_hash', 64)->nullable()->after('fichier_valide_chemin');
            $table->timestamp('fichier_valide_scelle_le')->nullable()->after('fichier_valide_hash');
        });
    }

    public function down(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropColumn(['fichier_valide_hash', 'fichier_valide_scelle_le']);
        });
    }
};
