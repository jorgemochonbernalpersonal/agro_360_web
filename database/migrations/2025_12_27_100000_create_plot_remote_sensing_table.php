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
        Schema::create('plot_remote_sensing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained()->onDelete('cascade');
            $table->date('image_date');
            
            // Índice NDVI (Normalized Difference Vegetation Index)
            $table->decimal('ndvi_mean', 6, 4)->nullable();
            $table->decimal('ndvi_min', 6, 4)->nullable();
            $table->decimal('ndvi_max', 6, 4)->nullable();
            $table->decimal('ndvi_stddev', 6, 4)->nullable();
            
            // Índice NDWI (Normalized Difference Water Index)
            $table->decimal('ndwi_mean', 6, 4)->nullable();
            $table->decimal('ndwi_min', 6, 4)->nullable();
            $table->decimal('ndwi_max', 6, 4)->nullable();
            
            // Índice EVI (Enhanced Vegetation Index)
            $table->decimal('evi_mean', 6, 4)->nullable();
            
            // Metadatos de la imagen
            $table->integer('cloud_coverage')->nullable()->comment('Porcentaje de nubes 0-100');
            $table->string('image_source', 50)->default('sentinel2');
            $table->string('tile_id', 100)->nullable()->comment('ID del tile de Sentinel-2');
            $table->string('tile_path')->nullable()->comment('Ruta al tile NDVI procesado');
            
            // Estado de salud calculado
            $table->enum('health_status', ['excellent', 'good', 'moderate', 'poor', 'critical'])->nullable();
            $table->text('health_notes')->nullable();
            
            // Comparativa con periodo anterior
            $table->decimal('ndvi_change', 6, 4)->nullable()->comment('Cambio vs periodo anterior');
            $table->enum('trend', ['increasing', 'stable', 'decreasing'])->nullable();
            
            // Metadatos adicionales
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Índices para consultas rápidas
            $table->index(['plot_id', 'image_date']);
            $table->unique(['plot_id', 'image_date', 'image_source']);
            $table->index('health_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_remote_sensing');
    }
};
