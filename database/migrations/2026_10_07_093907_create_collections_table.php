<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')
                ->constrained('shops')
                ->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180);
            $table->text('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('status', 20)->default('draft'); // draft | published
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['shop_id', 'slug']);
            $table->index(['shop_id', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
