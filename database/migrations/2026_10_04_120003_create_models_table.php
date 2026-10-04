<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('models', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('shop_id')
                ->constrained('shops')
                ->cascadeOnDelete();

            $table->foreignUuid('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->string('name', 150);
            $table->string('slug', 180)->nullable(); // identifiant public lisible (optionnel)
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->string('color', 50)->nullable();
            $table->string('reference', 50)->nullable();
            $table->string('status', 20)->default('draft'); // draft | published | archived
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // 🔑🔑🔑 INDEX CRITIQUES POUR LA PERFORMANCE MULTI-TENANT
            // Toujours shop_id en PREMIER, sinon on scanne toute la table.
            $table->index(['shop_id', 'status', 'published_at']);
            $table->index(['shop_id', 'category_id', 'status']);
            $table->index(['shop_id', 'created_at']);

            // Unicité du slug par boutique (un styliste peut avoir
            // "robe-soiree" sans conflit avec un autre styliste)
            $table->unique(['shop_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('models');
    }
};
