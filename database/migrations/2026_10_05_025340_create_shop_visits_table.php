<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shop_id')
                ->constrained('shops')
                ->cascadeOnDelete();
            $table->foreignUuid('model_id')
                ->nullable()
                ->constrained('models')
                ->nullOnDelete();
            $table->string('event', 30); // view_shop | view_model | whatsapp_click
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Index composite CRITIQUE pour les stats rapides
            $table->index(['shop_id', 'event', 'created_at']);
            $table->index(['model_id', 'event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_visits');
    }
};
