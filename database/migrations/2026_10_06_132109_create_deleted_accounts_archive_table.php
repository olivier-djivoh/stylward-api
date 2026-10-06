<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deleted_accounts_archive', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Hash de l'email (pour détecter les réinscriptions)
            $table->string('email_hash', 64)->index();

            // Informations anonymisées (non personnelles)
            $table->string('shop_name', 150)->nullable();
            $table->string('shop_slug', 100)->nullable();
            $table->unsignedInteger('shop_views_total')->default(0);
            $table->unsignedInteger('shop_visitors_total')->default(0);
            $table->unsignedInteger('shop_whatsapp_clicks')->default(0);
            $table->unsignedInteger('models_count')->default(0);
            $table->unsignedInteger('published_models_count')->default(0);

            // Motif de suppression
            $table->string('deletion_reason', 50)->nullable();
            $table->text('deletion_reason_detail')->nullable();

            // Dates
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('deletion_requested_at')->nullable();
            $table->timestamp('deleted_at')->useCurrent();

            $table->index('deleted_at');
            $table->index('deletion_reason');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deleted_accounts_archive');
    }
};
