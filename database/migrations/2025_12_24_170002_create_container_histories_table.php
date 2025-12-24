<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si la tabla ya existe (por si hubo una ejecución parcial anterior)
        if (Schema::hasTable('container_histories')) {
            return;
        }

        Schema::create('container_histories', function (Blueprint $table) {
            $table->id();

            // Contenedor
            $table->foreignId('container_id')->constrained('containers')->onDelete('cascade');

            // Referencias al contenido
            $table->unsignedBigInteger('wine_id')->nullable()->comment('ID de vino (tabla futura)');
            $table->unsignedBigInteger('wine_process_detail_id')->nullable()->comment('Proceso de vinificación');
            $table->foreignId('harvest_id')->nullable()->constrained('harvests')->onDelete('set null');
            $table->unsignedBigInteger('external_grape_id')->nullable()->comment('Uva comprada externa');

            // Control de subproductos
            $table->boolean('has_subproducts')->default(false);

            // Actividad de campo relacionada
            $table->foreignId('field_activity_id')->nullable()->constrained('agricultural_activities')->onDelete('set null');

            // Tipo de operación
            $table->enum('operation_type', [
                'fill',  // Llenado
                'empty',  // Vaciamiento
                'transfer',  // Transferencia
                'sale',  // Venta
                'adjustment',  // Ajuste
                'maintenance',  // Mantenimiento
            ])->default('fill');

            // Usuario que realizó la operación
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            // Cantidad (positiva para entrada, negativa para salida)
            $table->decimal('quantity', 10, 2)->comment('Cantidad en kg (+ entrada, - salida)');

            // Fechas
            $table->datetime('start_date')->comment('Fecha inicio de la operación');
            $table->datetime('end_date')->nullable()->comment('Fecha fin de la operación');

            $table->timestamps();

            // Índices
            $table->index('container_id');
            $table->index('harvest_id');
            $table->index('wine_id');
            $table->index('operation_type');
            $table->index('start_date');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_histories');
    }
};
