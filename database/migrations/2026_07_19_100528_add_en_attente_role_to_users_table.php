<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN','CHEF LOT','CONTROLEUR','EN_ATTENTE') NOT NULL DEFAULT 'EN_ATTENTE'");
    }

    public function down(): void
    {
        // Attention : ne pas exécuter le rollback s'il existe déjà des users en EN_ATTENTE,
        // ça ferait échouer le MODIFY COLUMN (valeur hors enum restreint)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN','CHEF LOT','CONTROLEUR') NOT NULL DEFAULT 'CONTROLEUR'");
    }
};
