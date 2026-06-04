<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = Schema::getConnection();

        // Usar SQL crudo con IF NOT EXISTS para ser idempotente
        // Índice compuesto para búsquedas comunes: viticulturist_id + active
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_plots_viticulturist_active ON plots (viticulturist_id, active)');

        // Índice para búsquedas por nombre
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_plots_name ON plots (name)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection();

        $connection->statement('DROP INDEX IF EXISTS idx_plots_viticulturist_active ON plots');
        $connection->statement('DROP INDEX IF EXISTS idx_plots_name ON plots');
    }
};
