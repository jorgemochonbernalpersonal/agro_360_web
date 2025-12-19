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
        Schema::create('multiple_plot_sigpac', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained('plots')->onDelete('cascade');
            $table->unsignedBigInteger('sigpac_id');
            $table->foreignId('plot_geometry_id')->nullable()->constrained('plot_geometry')->onDelete('set null');
            $table->timestamps();
            
            $table->foreign('sigpac_id')->references('id')->on('sigpacs')->onDelete('cascade');
            
            // Índice único para evitar duplicados (permitir múltiples geometrías para mismo plot-sigpac)
            $table->unique(['plot_id', 'sigpac_id', 'plot_geometry_id'], 'unique_plot_sigpac_geometry');
            $table->index('plot_id');
            $table->index('sigpac_id');
            $table->index('plot_geometry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multiple_plot_sigpac');
    }
};

