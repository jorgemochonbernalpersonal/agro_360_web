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
        Schema::create('harvests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('agricultural_activities')->onDelete('cascade');
            $table->foreignId('plot_planting_id')->nullable()->constrained('plot_plantings')->onDelete('set null');
            
            // Fechas
            $table->date('harvest_start_date')->comment('Fecha inicio de la vendimia');
            $table->date('harvest_end_date')->nullable()->comment('Fecha fin de la vendimia (opcional)');
            
            // Cantidad cosechada
            $table->decimal('total_weight', 10, 2)->comment('Peso total cosechado (kg)');
            $table->decimal('yield_per_hectare', 10, 2)->nullable()->comment('Rendimiento kg/ha (calculado automáticamente)');
            
            // Calidad de la uva (opcional)
            $table->decimal('baume_degree', 5, 2)->nullable()->comment('Grados Baumé');
            $table->decimal('brix_degree', 5, 2)->nullable()->comment('Grados Brix');
            $table->decimal('acidity_level', 5, 2)->nullable()->comment('Acidez total (g/L)');
            $table->decimal('ph_level', 4, 2)->nullable()->comment('pH');
            
            // Evaluación organoléptica (opcional)
            $table->enum('color_rating', ['excelente', 'bueno', 'aceptable', 'deficiente'])->nullable();
            $table->enum('aroma_rating', ['excelente', 'bueno', 'aceptable', 'deficiente'])->nullable();
            $table->enum('health_status', ['sano', 'daño_leve', 'daño_moderado', 'daño_grave'])->nullable();
            
            // Destino
            $table->enum('destination_type', ['winery', 'direct_sale', 'cooperative', 'self_consumption', 'other'])->nullable();
            $table->string('destination')->nullable()->comment('Nombre específico del destino (bodega, comprador, etc.)');
            $table->string('buyer_name')->nullable()->comment('Nombre del comprador');
            
            // Valor económico (opcional)
            $table->decimal('price_per_kg', 10, 4)->nullable()->comment('Precio por kilogramo (€/kg)');
            $table->decimal('total_value', 12, 2)->nullable()->comment('Valor total calculado (€)');
            
            // Metadatos de edición (auditoría)
            $table->timestamp('edited_at')->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('edit_notes')->nullable()->comment('Motivo de edición');
            
            // Estado
            $table->enum('status', ['active', 'cancelled', 'draft'])->default('active');
            
            // Notas adicionales
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index('plot_planting_id');
            $table->index('harvest_start_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
