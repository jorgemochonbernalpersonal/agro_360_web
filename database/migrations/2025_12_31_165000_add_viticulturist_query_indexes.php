<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Añade índices compuestos para optimizar queries de viticultores
     * Mejora significativa en rendimiento de Plot::forViticulturist y WineryViticulturist::visibleTo
     */
    public function up(): void
    {
        $connection = Schema::getConnection();
        
        // winery_viticulturist indexes
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_wv_parent_source ON winery_viticulturist (parent_viticulturist_id, source)');
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_wv_supervisor_source ON winery_viticulturist (supervisor_id, source)');
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_wv_winery_source ON winery_viticulturist (winery_id, source)');
        
        // plots indexes
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_plots_viticulturist_active ON plots (viticulturist_id, active)');
        
        // agricultural_activities indexes
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_activities_vit_date ON agricultural_activities (viticulturist_id, activity_date)');
        $connection->statement('CREATE INDEX IF NOT EXISTS idx_activities_vit_created ON agricultural_activities (viticulturist_id, created_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection();
        
        // Drop indexes if they exist
        $connection->statement('DROP INDEX IF EXISTS idx_wv_parent_source ON winery_viticulturist');
        $connection->statement('DROP INDEX IF EXISTS idx_wv_supervisor_source ON winery_viticulturist');
        $connection->statement('DROP INDEX IF EXISTS idx_wv_winery_source ON winery_viticulturist');
        $connection->statement('DROP INDEX IF EXISTS idx_plots_viticulturist_active ON plots');
        $connection->statement('DROP INDEX IF EXISTS idx_activities_vit_date ON agricultural_activities');
        $connection->statement('DROP INDEX IF EXISTS idx_activities_vit_created ON agricultural_activities');
    }
};
