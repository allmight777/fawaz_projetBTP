<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'EN_ATTENTE'");
        } else {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN','CHEF LOT','CONTROLEUR','EN_ATTENTE') NOT NULL DEFAULT 'EN_ATTENTE'");
        }
    }

    public function down(): void
    {
        // Attention : ne pas exécuter le rollback s'il existe déjà des users en EN_ATTENTE,
        // ça ferait échouer le MODIFY COLUMN (valeur hors enum restreint)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'CONTROLEUR'");
        } else {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN','CHEF LOT','CONTROLEUR') NOT NULL DEFAULT 'CONTROLEUR'");
        }
    }
};
