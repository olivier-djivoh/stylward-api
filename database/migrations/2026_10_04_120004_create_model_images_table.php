<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_images', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('model_id')
                ->constrained('models')
                ->cascadeOnDelete();

            $table->string('path');
            $table->string('type', 20)->default('gallery'); // main | gallery
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('alt_text', 255)->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->timestamps();

            // Un modèle ne peut avoir qu'UNE seule image principale
            $table->unique(['model_id', 'type', 'sort_order']);

            $table->index(['model_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_images');
    }
};
