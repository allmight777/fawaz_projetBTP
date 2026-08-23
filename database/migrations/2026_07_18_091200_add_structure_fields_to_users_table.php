<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('structure_id')->nullable()->after('lot_id')->constrained('structures')->nullOnDelete();
            $table->enum('categorie_role', ['responsable_organisme', 'collaborateur'])->nullable()->after('structure_id');
            $table->string('fonction')->nullable()->after('categorie_role');
            $table->string('specialite')->nullable()->after('fonction');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('structure_id');
            $table->dropColumn(['categorie_role', 'fonction', 'specialite']);
        });
    }
};
