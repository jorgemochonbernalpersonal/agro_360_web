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
        Schema::create('estimated_yields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_planting_id')->constrained('plot_plantings')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('estimated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Rendimiento estimado
            $table->decimal('estimated_yield_per_hectare', 10, 2)->comment('Rendimiento estimado por hectárea (kg/ha)');
            $table->decimal('estimated_total_yield', 10, 2)->comment('Rendimiento total estimado (kg)');
            
            // Fecha y método de estimación
            $table->date('estimation_date')->comment('Fecha de la estimación');
            $table->enum('estimation_method', ['visual', 'sampling', 'historical', 'satellite', 'other'])->default('visual')->comment('Método de estimación');
            
            // Estado
            $table->enum('status', ['draft', 'confirmed', 'archived'])->default('draft')->comment('Estado de la estimación');
            
            // Comparación con rendimiento real (se actualiza después de la cosecha)
            $table->decimal('actual_yield_per_hectare', 10, 2)->nullable()->comment('Rendimiento real por hectárea (kg/ha) - se actualiza después de la cosecha');
            $table->decimal('actual_total_yield', 10, 2)->nullable()->comment('Rendimiento total real (kg) - se actualiza después de la cosecha');
            $table->decimal('variance_percentage', 5, 2)->nullable()->comment('Diferencia porcentual entre estimado y real');
            
            // Notas
            $table->text('notes')->nullable()->comment('Notas sobre la estimación');
            
            $table->timestamps();
            
            // Índices
            $table->index(['plot_planting_id', 'campaign_id']);
            $table->index('estimation_date');
            $table->index('status');
            
            // Evitar duplicados: una estimación por plantación y campaña
            $table->unique(['plot_planting_id', 'campaign_id'], 'unique_plot_planting_campaign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimated_yields');
    }
};
