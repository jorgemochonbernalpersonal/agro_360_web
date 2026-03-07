<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_maintenance_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_maintenance_id')->constrained('container_maintenances')->cascadeOnDelete();
            $table->foreignId('winery_supply_id')->nullable()->constrained('winery_supplies')->nullOnDelete();
            $table->string('supply_name')->nullable()->comment('Nombre libre si no está en el catálogo');
            $table->decimal('quantity_used', 12, 3);
            $table->foreignId('unit_of_measurement_id')->nullable()->constrained('units_of_measurement')->nullOnDelete();
            $table->decimal('cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('container_maintenance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_maintenance_supplies');
    }
};
