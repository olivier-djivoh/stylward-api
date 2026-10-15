<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_model', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('collection_id')
                ->constrained('collections')
                ->cascadeOnDelete();
            $table->foreignUuid('model_id')
                ->constrained('models')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['collection_id', 'model_id']);
            $table->index(['collection_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_model');
    }
};
