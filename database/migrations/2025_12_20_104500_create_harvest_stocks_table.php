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
        Schema::create('harvest_stocks', function (Blueprint $table) {
            $table->id();
            
            // Referencias
            $table->foreignId('harvest_id')->constrained('harvests')->onDelete('cascade');
            $table->foreignId('container_id')->nullable()->constrained('harvest_containers')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->onDelete('set null');
            
            // Tipo de movimiento
            $table->enum('movement_type', [
                'initial',      // Registro inicial al crear harvest
                'adjustment',   // Ajuste manual (edición)
                'reserve',      // Reserva para venta
                'sale',         // Venta confirmada
                'unreserve',    // Cancelación de reserva
                'gift',         // Regalo/donación
                'loss',         // Pérdida/merma
                'return'        // Devolución
            ])->default('initial');
            
            // Cantidades - Cambio y resultado
            $table->decimal('quantity_change', 10, 3)->comment('Cambio en cantidad (+ o -)');
            $table->decimal('quantity_after', 10, 3)->comment('Cantidad total después del movimiento');
            
            // Desglose del stock después del movimiento
            $table->decimal('available_qty', 10, 3)->default(0.000)->comment('Disponible para venta');
            $table->decimal('reserved_qty', 10, 3)->default(0.000)->comment('Reservado (pendiente factura)');
            $table->decimal('sold_qty', 10, 3)->default(0.000)->comment('Vendido (facturado)');
            $table->decimal('gifted_qty', 10, 3)->default(0.000)->comment('Regalado');
            $table->decimal('lost_qty', 10, 3)->default(0.000)->comment('Pérdidas/mermas');
            
            // Metadatos
            $table->text('notes')->nullable()->comment('Razón del movimiento');
            $table->string('reference_number', 100)->nullable()->comment('Número de referencia externo');
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('harvest_id');
            $table->index('container_id');
            $table->index('user_id');
            $table->index('invoice_item_id');
            $table->index('movement_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_stocks');
    }
};
