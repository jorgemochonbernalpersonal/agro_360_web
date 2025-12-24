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
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            
            // Usuario propietario
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Proveedor (opcional)
            $table->unsignedBigInteger('supplier_id')->nullable()->comment('ID del proveedor (tabla futura)');
            
            // Sala/ubicación del contenedor (opcional)
            $table->unsignedBigInteger('container_room_id')->nullable()->comment('ID de la sala (tabla futura)');
            
            // Información básica
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('photos')->nullable();
            $table->string('thumbnail_img')->nullable();
            
            // Capacidad
            $table->decimal('capacity', 10, 2)->comment('Capacidad total en kg');
            $table->decimal('used_capacity', 10, 2)->default(0.00)->comment('Capacidad usada en kg');
            
            // Cantidad y número de serie
            $table->integer('quantity')->default(1)->comment('Cantidad de unidades');
            $table->string('serial_number')->nullable()->comment('Número de serie');
            
            // IDs de referencia (opcionales, para tablas futuras)
            $table->unsignedBigInteger('unit_of_measurement_id')->default(1)->comment('Unidad de medida (kg, L, etc.)');
            $table->unsignedBigInteger('type_id')->default(1)->comment('Tipo de contenedor (barril, tanque, etc.)');
            $table->unsignedBigInteger('material_id')->default(1)->comment('Material (acero, roble, etc.)');
            
            // Propiedades específicas (para barriles de roble)
            $table->string('oak_type')->nullable()->comment('Tipo de roble');
            $table->string('toast_type')->nullable()->comment('Tipo de tostado');
            
            // Fechas
            $table->date('purchase_date')->nullable()->comment('Fecha de compra');
            $table->datetime('next_maintenance_date')->nullable()->comment('Próximo mantenimiento');
            
            // Posición en almacén (opcional)
            $table->integer('x_position')->nullable()->comment('Posición X en almacén');
            $table->integer('y_position')->nullable()->comment('Posición Y en almacén');
            
            // Estado
            $table->boolean('archived')->default(false)->comment('Archivado');
            
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('capacity');
            $table->index('used_capacity');
            $table->index('archived');
            $table->index(['user_id', 'archived']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
