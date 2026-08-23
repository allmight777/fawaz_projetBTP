<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained('document_types')->cascadeOnDelete();
            $table->string('libelle');
            $table->unsignedInteger('ordre')->default(0);
            $table->boolean('obligatoire')->default(true);
            $table->timestamps();

            $table->index('document_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
