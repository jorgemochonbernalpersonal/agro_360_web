<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE plot_plantings MODIFY COLUMN right_type ENUM('nueva', 'replantacion', 'conversion', 'transferencia') NULL");
    }

    public function down(): void
    {
        // Remove any 'transferencia' rows before reverting to avoid constraint errors
        DB::statement("UPDATE plot_plantings SET right_type = NULL WHERE right_type = 'transferencia'");
        DB::statement("ALTER TABLE plot_plantings MODIFY COLUMN right_type ENUM('nueva', 'replantacion', 'conversion') NULL");
    }
};
