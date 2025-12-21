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
        Schema::table('agricultural_activities', function (Blueprint $table) {
            // Añadir plot_planting_id NULLABLE después de plot_id
            // Usamos plot_planting_id para ser consistente con el modelo PlotPlanting
            $table->foreignId('plot_planting_id')
                  ->nullable()
                  ->after('plot_id')
                  ->constrained('plot_plantings')
                  ->onDelete('cascade');
            
            // Índice para optimizar queries
            $table->index('plot_planting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->dropForeign(['plot_planting_id']);
            $table->dropIndex(['plot_planting_id']);
            $table->dropColumn('plot_planting_id');
        });
    }
};
