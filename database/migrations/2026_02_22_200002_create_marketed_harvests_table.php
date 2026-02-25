<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketed_harvests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('harvest_id')->constrained('harvests')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');

            $table->date('delivery_date')->comment('Fecha de la entrega/albarán');
            $table->decimal('quantity_kg', 10, 2)->comment('Kilogramos entregados en esta entrega');

            $table->enum('destination_type', [
                'own_winery',    // Bodega propia
                'cooperative',   // Cooperativa
                'third_party',   // Terceros (bodega compradora)
                'other',
            ])->default('cooperative');

            $table->string('buyer_name')->nullable()->comment('Nombre de la bodega/cooperativa/comprador');
            $table->string('buyer_rega_code', 30)->nullable()->comment('Código REGA de la bodega receptora');
            $table->string('transport_document', 50)->nullable()->comment('Nº albarán de transporte');
            $table->string('vehicle_plate', 15)->nullable()->comment('Matrícula del vehículo');
            $table->decimal('price_per_kg', 8, 4)->nullable()->comment('Precio €/kg en esta entrega');
            $table->decimal('total_value', 12, 2)->nullable()->comment('Valor total calculado (€)');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['harvest_id', 'active']);
            $table->index(['campaign_id', 'delivery_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketed_harvests');
    }
};
