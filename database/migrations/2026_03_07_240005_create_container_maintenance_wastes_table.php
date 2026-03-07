<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_maintenance_wastes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_maintenance_id')->constrained('container_maintenances')->cascadeOnDelete();
            $table->foreignId('container_waste_type_id')->nullable()->constrained('container_waste_types')->nullOnDelete();
            $table->string('custom_waste_type')->nullable()->comment('Descripción libre cuando tipo = Otro');
            $table->date('waste_date');
            $table->decimal('quantity', 12, 3)->nullable();
            $table->foreignId('unit_of_measurement_id')->nullable()->constrained('units_of_measurement')->nullOnDelete();
            $table->string('disposal_method')->nullable()->comment('Ej: gestor autorizado, vertedero, compostaje');
            $table->decimal('cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('container_maintenance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_maintenance_wastes');
    }
};
