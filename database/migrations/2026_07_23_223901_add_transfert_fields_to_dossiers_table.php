<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('dossiers', function (Blueprint $table) {
        $table->boolean('necessite_transfert_chef')->default(false)->after('statut');
        $table->string('structure_cible', 30)->nullable()->after('necessite_transfert_chef');
    });
}

public function down(): void
{
    Schema::table('dossiers', function (Blueprint $table) {
        $table->dropColumn(['necessite_transfert_chef', 'structure_cible']);
    });
}
};
