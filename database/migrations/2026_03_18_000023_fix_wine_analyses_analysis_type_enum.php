<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE wine_analyses MODIFY analysis_type ENUM('standard','complete','organic','custom') NOT NULL DEFAULT 'standard'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE wine_analyses MODIFY analysis_type ENUM('own','external') NOT NULL DEFAULT 'own'");
    }
};
