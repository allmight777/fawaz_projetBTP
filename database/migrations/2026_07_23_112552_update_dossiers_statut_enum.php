<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Colonne texte libre plutôt qu'un ENUM natif (comme users.role) : porte toutes
        // les valeurs utilisées par l'application (dont 'archive') et reste compatible
        // MySQL comme PostgreSQL, sans syntaxe ALTER spécifique à un seul moteur.
        if (DB::getDriverName() === 'pgsql') {
            // Le enum() d'origine crée une contrainte CHECK côté Postgres (ex:
            // dossiers_statut_check) qui restreint toujours les valeurs même après
            // un simple ALTER COLUMN TYPE : il faut la supprimer explicitement pour
            // que la colonne devienne vraiment libre (ex: statut 'en_cours').
            DB::statement("ALTER TABLE dossiers DROP CONSTRAINT IF EXISTS dossiers_statut_check");
            DB::statement("ALTER TABLE dossiers ALTER COLUMN statut TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE dossiers ALTER COLUMN statut SET DEFAULT 'brouillon'");
        } else {
            DB::statement("ALTER TABLE dossiers MODIFY COLUMN statut VARCHAR(30) NOT NULL DEFAULT 'brouillon'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE dossiers ALTER COLUMN statut TYPE VARCHAR(30)");
        } else {
            DB::statement("ALTER TABLE dossiers MODIFY COLUMN statut VARCHAR(30) NOT NULL DEFAULT 'brouillon'");
        }
    }
};
