<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nouvelle structure « Maître d'Œuvre » : le type de structure et les rôles passent en
        // texte libre (comme dossiers.statut) plutôt que de rallonger un ENUM à chaque structure.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE structures DROP CONSTRAINT IF EXISTS structures_type_check');
            DB::statement('ALTER TABLE structures ALTER COLUMN type TYPE VARCHAR(30)');
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50)');
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite (tests) : change() reconstruit la table, ce qui supprime la contrainte CHECK de l'enum.
            Schema::table('structures', fn (Blueprint $table) => $table->string('type', 30)->change());
            Schema::table('users', fn (Blueprint $table) => $table->string('role', 50)->default('EN_ATTENTE')->change());
        } else {
            DB::statement('ALTER TABLE structures MODIFY COLUMN type VARCHAR(30) NOT NULL');
            DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'EN_ATTENTE'");
        }

        if (!DB::table('structures')->where('type', 'maitre_oeuvre')->exists()) {
            DB::table('structures')->insert([
                'nom' => "Maître d'Œuvre",
                'type' => 'maitre_oeuvre',
                'actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Circuit de visa : une ligne par structure dont le visa est demandé pour une version.
        // Chaque structure vise à son niveau, simultanément ; le document n'est validé que
        // lorsque toutes les lignes sont validées.
        Schema::create('circuit_visas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_version_id')->constrained()->cascadeOnDelete();
            $table->string('structure_type', 30); // bureau_controle | bureau_etudes | maitre_oeuvre
            $table->string('statut', 20)->default('en_attente'); // en_attente | en_analyse | valide | a_corriger
            $table->foreignId('decide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decide_le')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->unique(['document_version_id', 'structure_type']);
            $table->index('dossier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circuit_visas');
    }
};
