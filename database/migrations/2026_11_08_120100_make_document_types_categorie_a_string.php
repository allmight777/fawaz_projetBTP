<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** La catégorie devient une simple clé texte : l'admin peut créer de nouvelles catégories. */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE document_types DROP CONSTRAINT IF EXISTS document_types_categorie_check');
            DB::statement('ALTER TABLE document_types ALTER COLUMN categorie TYPE VARCHAR(60)');

            return;
        }

        DB::statement('ALTER TABLE document_types MODIFY categorie VARCHAR(60) NOT NULL');
    }

    public function down(): void
    {
        // Retour à l'ENUM non appliqué : des catégories personnalisées pourraient exister.
    }
};
