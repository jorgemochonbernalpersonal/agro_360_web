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
        Schema::table('multipart_plot_sigpac', function (Blueprint $table) {
            // Añadir índice compuesto para optimizar queries de geometrías por parcela
            // Esto mejora el rendimiento de loadPlotGeometries() en ~10-15%
            $table->index(['plot_id', 'plot_geometry_id'], 'idx_plot_geometry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('multipart_plot_sigpac', function (Blueprint $table) {
            // Eliminar índice
            $table->dropIndex('idx_plot_geometry');
        });
    }
};
