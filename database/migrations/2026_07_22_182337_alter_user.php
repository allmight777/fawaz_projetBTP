<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // La migration précédente (2026_07_19) a déjà retiré la contrainte ENUM
            // côté pgsql : role est une VARCHAR libre, rien à faire de plus ici.
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'EN_ATTENTE'");

            return;
        }

        // Modifier la colonne role pour accepter les nouveaux rôles
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'ADMIN',
            'CHEF LOT',
            'CONTROLEUR',
            'EN_ATTENTE',
            'ENTREPRISE_CHEF',
            'ENTREPRISE_COLLABORATEUR',
            'BUREAU_CONTROLE_CHEF',
            'BUREAU_CONTROLE_COLLABORATEUR',
            'BUREAU_ETUDES_CHEF',
            'BUREAU_ETUDES_COLLABORATEUR',
            'MAITRE_OUVRAGE_CHEF',
            'MAITRE_OUVRAGE_COLLABORATEUR'
        ) NOT NULL DEFAULT 'EN_ATTENTE'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            return;
        }

        // Revenir à l'ancien ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'ADMIN',
            'CHEF LOT',
            'CONTROLEUR',
            'EN_ATTENTE'
        ) NOT NULL DEFAULT 'EN_ATTENTE'");
    }
};
