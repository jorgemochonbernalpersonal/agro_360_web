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

        // Helper para crear índices de forma idempotente usando SQL crudo
        $createIndexIfNotExists = function ($table, $columns, $indexName) use ($connection) {
            $columnsList = is_array($columns) ? implode(', ', $columns) : $columns;
            $connection->statement("CREATE INDEX IF NOT EXISTS {$indexName} ON {$table} ({$columnsList})");
        };

        // Índices para la tabla plots
        $createIndexIfNotExists('plots', 'viticulturist_id', 'idx_plots_viticulturist');
        $createIndexIfNotExists('plots', 'active', 'idx_plots_active');
        $createIndexIfNotExists('plots', 'municipality_id', 'idx_plots_municipality');

        // Índices para users
        $createIndexIfNotExists('users', 'role', 'idx_users_role');
        $createIndexIfNotExists('users', 'role, email', 'idx_users_role_email');

        // Índices para winery_viticulturist
        $createIndexIfNotExists('winery_viticulturist', 'winery_id', 'idx_wv_winery');
        $createIndexIfNotExists('winery_viticulturist', 'viticulturist_id', 'idx_wv_viticulturist');
        $createIndexIfNotExists('winery_viticulturist', 'supervisor_id', 'idx_wv_supervisor');
        $createIndexIfNotExists('winery_viticulturist', 'parent_viticulturist_id', 'idx_wv_parent');
        $createIndexIfNotExists('winery_viticulturist', 'source', 'idx_wv_source');

        // Índices para agricultural_activities (si la tabla existe)
        if (Schema::hasTable('agricultural_activities')) {
            $createIndexIfNotExists('agricultural_activities', 'plot_id', 'idx_aa_plot');
            $createIndexIfNotExists('agricultural_activities', 'activity_type', 'idx_aa_type');
            $createIndexIfNotExists('agricultural_activities', 'activity_date', 'idx_aa_date');
            $createIndexIfNotExists('agricultural_activities', 'plot_id, activity_date', 'idx_aa_plot_date');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection();

        // Drop indexes using raw SQL
        $connection->statement('DROP INDEX IF EXISTS idx_plots_viticulturist ON plots');
        $connection->statement('DROP INDEX IF EXISTS idx_plots_active ON plots');
        $connection->statement('DROP INDEX IF EXISTS idx_plots_municipality ON plots');

        $connection->statement('DROP INDEX IF EXISTS idx_users_role ON users');
        $connection->statement('DROP INDEX IF EXISTS idx_users_role_email ON users');

        $connection->statement('DROP INDEX IF EXISTS idx_wv_winery ON winery_viticulturist');
        $connection->statement('DROP INDEX IF EXISTS idx_wv_viticulturist ON winery_viticulturist');
        $connection->statement('DROP INDEX IF EXISTS idx_wv_supervisor ON winery_viticulturist');
        $connection->statement('DROP INDEX IF EXISTS idx_wv_parent ON winery_viticulturist');
        $connection->statement('DROP INDEX IF EXISTS idx_wv_source ON winery_viticulturist');

        if (Schema::hasTable('agricultural_activities')) {
            $connection->statement('DROP INDEX IF EXISTS idx_aa_plot ON agricultural_activities');
            $connection->statement('DROP INDEX IF EXISTS idx_aa_type ON agricultural_activities');
            $connection->statement('DROP INDEX IF EXISTS idx_aa_date ON agricultural_activities');
            $connection->statement('DROP INDEX IF EXISTS idx_aa_plot_date ON agricultural_activities');
        }
    }
};
