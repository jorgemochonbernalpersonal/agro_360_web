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
        Schema::create('harvest_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('harvest_id')->constrained('harvests')->onDelete('cascade');
            
            // Tipo de contenedor
            $table->enum('container_type', ['caja', 'pallet', 'contenedor', 'saco', 'cuba', 'other'])->default('caja')->comment('Tipo de contenedor');
            $table->string('container_number')->nullable()->comment('Número o identificador del contenedor');
            
            // Cantidad y peso
            $table->integer('quantity')->default(1)->comment('Cantidad de contenedores');
            $table->decimal('weight', 10, 2)->comment('Peso total en kg');
            $table->decimal('weight_per_unit', 10, 2)->nullable()->comment('Peso por unidad (kg)');
            
            // Ubicación y estado
            $table->string('location')->nullable()->comment('Ubicación del contenedor (almacén, campo, etc.)');
            $table->enum('status', ['filled', 'in_transit', 'delivered', 'stored', 'empty'])->default('filled')->comment('Estado del contenedor');
            
            // Fechas
            $table->date('filled_date')->nullable()->comment('Fecha de llenado');
            $table->date('delivery_date')->nullable()->comment('Fecha de entrega');
            
            // Notas
            $table->text('notes')->nullable()->comment('Notas adicionales');
            
            $table->timestamps();
            
            // Índices
            $table->index('harvest_id');
            $table->index('container_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_containers');
    }
};
