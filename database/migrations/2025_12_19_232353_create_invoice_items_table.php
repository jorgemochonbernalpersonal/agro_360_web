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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            
            // Relación con cosecha (principal)
            $table->foreignId('harvest_id')->nullable()->constrained('harvests')->onDelete('set null');
            
            // Descripción del item
            $table->string('name')->comment('Nombre del concepto');
            $table->text('description')->nullable();
            $table->string('sku')->nullable();
            $table->enum('concept_type', ['harvest', 'service', 'product', 'other'])->default('harvest');
            
            // Cantidad y precio
            $table->decimal('quantity', 10, 3)->default(1)->comment('Cantidad (kg, unidades, etc.)');
            $table->decimal('unit_price', 10, 4)->comment('Precio unitario');
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('Descuento %');
            $table->decimal('discount_amount', 10, 3)->default(0)->comment('Monto de descuento');
            
            // Impuestos
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->onDelete('set null');
            $table->string('tax_name')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_base', 10, 3)->default(0)->comment('Base imponible del item');
            $table->decimal('tax_amount', 10, 3)->default(0)->comment('Monto de impuesto del item');
            
            // Total
            $table->decimal('subtotal', 10, 3)->comment('Subtotal sin impuestos');
            $table->decimal('total', 10, 3)->comment('Total con impuestos');
            
            // Estado
            $table->enum('status', ['active', 'cancelled'])->default('active');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->enum('delivery_status', ['pending', 'in_transit', 'delivered', 'cancelled'])->default('pending');
            
            // Variaciones/opciones
            $table->json('variations')->nullable()->comment('Opciones/variaciones del item');
            
            $table->timestamps();
            
            $table->index('invoice_id');
            $table->index('harvest_id');
            $table->index('concept_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
