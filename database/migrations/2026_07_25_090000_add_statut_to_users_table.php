<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('statut', 20)->default('actif')->after('actif');
        });

        DB::table('users')->where('actif', true)->update(['statut' => 'actif']);
        DB::table('users')->where('actif', false)->update(['statut' => 'inactif']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};
