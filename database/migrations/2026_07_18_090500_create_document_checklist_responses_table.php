<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_checklist_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_version_id')->constrained('document_versions')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('checklist_items')->cascadeOnDelete();
            $table->boolean('coche')->default(false);
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->unique(['document_version_id', 'checklist_item_id'], 'doc_version_checklist_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_checklist_responses');
    }
};
