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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('restrict');
            $table->foreignId('client_address_id')->nullable()->constrained('client_addresses')->onDelete('set null');
            
            // Numeración
            $table->string('invoice_number')->unique()->comment('Número de factura único');
            $table->string('delivery_note_code')->nullable()->comment('Código de albarán');
            $table->integer('current_invoice_code')->default(1)->comment('Contador interno');
            $table->integer('current_delivery_note_code')->default(1);
            $table->dateTime('invoice_code_generated_at')->nullable();
            
            // Fechas
            $table->date('invoice_date');
            $table->date('due_date')->nullable()->comment('Fecha de vencimiento');
            $table->dateTime('delivery_note_date')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->dateTime('order_date')->default(now());
            
            // Dirección de facturación (snapshot)
            $table->text('billing_address')->nullable();
            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('billing_company_name')->nullable();
            $table->string('billing_company_document')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_country')->nullable();
            
            // Totales
            $table->decimal('subtotal', 12, 3)->default(0)->comment('Base imponible');
            $table->decimal('discount_amount', 12, 3)->default(0);
            $table->decimal('tax_base', 12, 3)->default(0)->comment('Base después de descuentos');
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('Tasa de impuesto aplicada');
            $table->decimal('tax_amount', 12, 3)->default(0)->comment('Monto de impuesto');
            $table->decimal('total_amount', 12, 3)->default(0);
            
            // Estado y pagos
            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled', 'corrective'])->default('draft');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');
            $table->enum('payment_type', ['cash', 'transfer', 'check', 'other'])->nullable();
            $table->text('payment_details')->nullable();
            
            // Información bancaria (si aplica)
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_routing_number')->nullable();
            $table->boolean('bank_payment_status')->default(false);
            
            // Entrega
            $table->enum('delivery_status', ['pending', 'in_transit', 'delivered', 'cancelled'])->default('pending');
            $table->string('tracking_code')->nullable();
            
            // SIF (Sistema de Facturación Electrónica - España)
            $table->enum('sif_status', ['pendiente', 'enviado', 'aceptado', 'error'])->default('pendiente');
            $table->string('sif_uuid')->nullable();
            $table->string('sif_hash')->nullable();
            $table->dateTime('sif_sent_at')->nullable();
            $table->text('sif_response')->nullable();
            $table->boolean('sif_excluded')->default(false);
            
            // Flags
            $table->boolean('is_verified_aet')->default(false)->comment('Verificado AET');
            $table->boolean('sent')->default(false);
            $table->boolean('viewed')->default(false);
            $table->boolean('delivery_viewed')->default(true);
            $table->boolean('payment_status_viewed')->default(true);
            $table->boolean('corrective')->default(false)->comment('Factura rectificativa');
            $table->boolean('gift')->default(false);
            
            // Observaciones
            $table->text('observations')->nullable()->comment('Observaciones generales');
            $table->text('observations_invoice')->nullable()->comment('Observaciones en factura');
            
            // Agrupación
            $table->foreignId('invoice_group_id')->nullable()->constrained('invoice_groups')->onDelete('set null');
            
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('client_id');
            $table->index('invoice_number');
            $table->index('status');
            $table->index('payment_status');
            $table->index('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
