<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['structure_id']);
            $table->dropColumn('structure_id');
        });

        Schema::create('event_structure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('structure_id')->constrained('structures')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'structure_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_structure');

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('structure_id')->nullable()->after('createur_id')->constrained('structures')->nullOnDelete();
        });
    }
};
