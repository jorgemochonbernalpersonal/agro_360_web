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
        Schema::create('container_states', function (Blueprint $table) {
            $table->id();
            
            // Contenedor único
            $table->foreignId('container_id')
                ->unique()
                ->constrained('harvest_containers')
                ->onDelete('cascade');
            
            // Contenido actual
            $table->enum('content_type', [
                'empty',
                'harvest',
                'wine',
                'external_grape',
                'mixed'
            ])->default('empty');
            
            // Referencias al contenido (solo una será NOT NULL según content_type)
            $table->foreignId('harvest_id')->nullable()->constrained('harvests')->onDelete('set null');
            $table->unsignedBigInteger('wine_id')->nullable()->comment('Para futura integración con bodega');
            $table->unsignedBigInteger('wine_process_id')->nullable()->comment('Proceso de vinificación');
            $table->unsignedBigInteger('external_grape_id')->nullable()->comment('Uva comprada externa');
            
            // Estado de stock
            $table->decimal('total_quantity', 10, 3)->default(0.000)->comment('Cantidad total en contenedor');
            $table->decimal('available_qty', 10, 3)->default(0.000)->comment('Disponible');
            $table->decimal('reserved_qty', 10, 3)->default(0.000)->comment('Reservado');
            $table->decimal('sold_qty', 10, 3)->default(0.000)->comment('Vendido');
            
            // Control de subproductos
            $table->boolean('has_subproducts')->default(false);
            
            // Estado físico
            $table->string('location')->nullable();
            $table->timestamp('last_movement_at')->nullable();
            $table->foreignId('last_movement_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Índices
            $table->index('container_id');
            $table->index('harvest_id');
            $table->index('content_type');
            $table->index('last_movement_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_states');
    }
};
