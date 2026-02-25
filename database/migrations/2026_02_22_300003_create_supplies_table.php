<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('phytosanitary_product_id')->nullable()
                ->constrained('phytosanitary_products')->onDelete('set null')
                ->comment('Enlace al catálogo de productos fitosanitarios');

            $table->string('name')->comment('Nombre del producto en el almacén');
            $table->string('commercial_name')->nullable();
            $table->string('registration_number', 50)->nullable()->comment('Nº Registro MAPA del producto');
            $table->enum('supply_type', [
                'phytosanitary',
                'fertilizer',
                'seed',
                'postharvest',
                'other',
            ])->default('phytosanitary');
            $table->string('unit_of_measurement', 20)->default('L')->comment('L, kg, g, mL, ud');

            // Stock
            $table->decimal('initial_stock', 10, 3)->default(0);
            $table->decimal('current_stock', 10, 3)->default(0);
            $table->decimal('min_stock_alert', 10, 3)->nullable()->comment('Alerta de stock mínimo');

            $table->date('expiry_date')->nullable();

            // Composición nutricional (fertilizantes)
            $table->decimal('nutrient_n', 5, 2)->nullable()->comment('% Nitrógeno');
            $table->decimal('nutrient_p2o5', 5, 2)->nullable()->comment('% Fósforo');
            $table->decimal('nutrient_k2o', 5, 2)->nullable()->comment('% Potasio');
            $table->decimal('nutrient_cao', 5, 2)->nullable()->comment('% Calcio');
            $table->decimal('nutrient_mgo', 5, 2)->nullable()->comment('% Magnesio');
            $table->decimal('nutrient_so3', 5, 2)->nullable()->comment('% Azufre');
            $table->decimal('organic_matter', 5, 2)->nullable()->comment('% Materia orgánica');

            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['viticulturist_id', 'active']);
            $table->index('supply_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
