<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'profession')) {
            $table->string('profession', 30)->default('stylist')->after('phone');
        }
        if (!Schema::hasColumn('users', 'gender')) {
            $table->string('gender', 10)->nullable()->after('profession');
        }
    });
}

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['profession']);
            $table->dropColumn(['profession', 'gender']);
        });
    }
};
