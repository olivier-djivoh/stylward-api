<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL : permet à la colonne id d'avoir une valeur par défaut
        DB::statement('ALTER TABLE collection_model ALTER COLUMN id SET DEFAULT gen_random_uuid()');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE collection_model ALTER COLUMN id DROP DEFAULT');
    }
};
