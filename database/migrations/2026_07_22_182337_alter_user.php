<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
        // Revenir à l'ancien ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'ADMIN',
            'CHEF LOT',
            'CONTROLEUR',
            'EN_ATTENTE'
        ) NOT NULL DEFAULT 'EN_ATTENTE'");
    }
};
