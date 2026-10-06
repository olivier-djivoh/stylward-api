<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_images', function (Blueprint $table) {
            $table->dropUnique(['model_id', 'type', 'sort_order']);
            $table->index(['model_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('model_images', function (Blueprint $table) {
            $table->dropIndex(['model_id', 'type']);
            $table->unique(['model_id', 'type', 'sort_order']);
        });
    }
};
