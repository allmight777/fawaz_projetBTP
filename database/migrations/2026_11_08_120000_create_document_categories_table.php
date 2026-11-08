<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('cle', 60)->unique(); // valeur stockée dans document_types.categorie
            $table->string('nom');
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
        });

        $defauts = [
            'etude_execution' => 'Étude d\'exécution',
            'gestion_projet' => 'Gestion de projet',
            'assurance_qualite' => 'Assurance qualité',
            'environnemental_social' => 'Environnemental et social',
            'bc_document' => 'Documents Bureau de Contrôle',
        ];
        $ordre = 0;
        foreach ($defauts as $cle => $nom) {
            DB::table('document_categories')->insert(['cle' => $cle, 'nom' => $nom, 'ordre' => ++$ordre, 'created_at' => now(), 'updated_at' => now()]);
        }

        // Catégories déjà utilisées par des types mais absentes de la liste : on les conserve.
        foreach (DB::table('document_types')->distinct()->pluck('categorie') as $cle) {
            if ($cle && !isset($defauts[$cle])) {
                DB::table('document_categories')->insert(['cle' => $cle, 'nom' => ucfirst(str_replace('_', ' ', $cle)), 'ordre' => ++$ordre, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_categories');
    }
};
