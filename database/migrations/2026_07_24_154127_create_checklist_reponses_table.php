<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('checklist_reponses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('document_version_id')->constrained('document_versions')->cascadeOnDelete();
        $table->foreignId('checklist_item_id')->constrained('checklist_items')->cascadeOnDelete();
        $table->boolean('valeur')->default(false);
        $table->foreignId('repondu_par')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();

        $table->unique(['document_version_id', 'checklist_item_id']);
    });
}

public function down(): void
{
    Schema::dropIfExists('checklist_reponses');
}
};
