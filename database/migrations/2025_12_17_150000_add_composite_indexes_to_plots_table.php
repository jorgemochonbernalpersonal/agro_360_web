<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            // Índice compuesto para búsquedas comunes: winery_id + active
            $table->index(['winery_id', 'active'], 'idx_plots_winery_active');
            
            // Índice compuesto para búsquedas comunes: viticulturist_id + active
            $table->index(['viticulturist_id', 'active'], 'idx_plots_viticulturist_active');
            
            // Índice compuesto para búsquedas comunes: winery_id + viticulturist_id + active
            $table->index(['winery_id', 'viticulturist_id', 'active'], 'idx_plots_winery_viticulturist_active');
            
            // Índice para búsquedas por nombre (si se usa frecuentemente)
            $table->index('name', 'idx_plots_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropIndex('idx_plots_winery_active');
            $table->dropIndex('idx_plots_viticulturist_active');
            $table->dropIndex('idx_plots_winery_viticulturist_active');
            $table->dropIndex('idx_plots_name');
        });
    }
};

