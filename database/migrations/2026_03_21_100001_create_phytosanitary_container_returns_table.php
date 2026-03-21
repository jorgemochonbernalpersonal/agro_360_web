<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phytosanitary_container_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('phytosanitary_product_id')->nullable()
                ->constrained('phytosanitary_products')->onDelete('set null');

            $table->date('date')->comment('Fecha de entrega en punto de recogida');
            $table->string('product_name')->comment('Nombre del producto (libre, puede diferir del catálogo)');
            $table->string('registration_number')->nullable()->comment('Nº registro MAPA del producto');
            $table->enum('container_type', ['plastic', 'glass', 'metal', 'cardboard', 'flexible', 'other'])
                ->comment('Material del envase');
            $table->decimal('container_size_liters', 8, 3)->nullable()->comment('Capacidad unitaria del envase (L)');
            $table->unsignedInteger('containers_quantity')->comment('Número de envases entregados');
            $table->decimal('total_weight_kg', 8, 3)->nullable()->comment('Peso total de envases vacíos (kg)');
            $table->enum('collection_system', ['sigfito', 'field', 'other'])->default('sigfito')
                ->comment('Sistema de gestión de envases');
            $table->string('collection_point')->comment('Nombre o código del punto de recogida');
            $table->string('transport_document')->nullable()->comment('Nº albarán o documento de transporte');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['viticulturist_id', 'active']);
            $table->index(['campaign_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phytosanitary_container_returns');
    }
};
