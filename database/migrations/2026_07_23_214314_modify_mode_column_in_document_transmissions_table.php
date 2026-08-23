<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier la colonne mode de ENUM à VARCHAR
        DB::statement("ALTER TABLE document_transmissions MODIFY mode VARCHAR(20) NOT NULL DEFAULT 'simple'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancien ENUM (si nécessaire)
        DB::statement("ALTER TABLE document_transmissions MODIFY mode ENUM('simple', 'diffusion_validation') NOT NULL DEFAULT 'simple'");
    }
};
