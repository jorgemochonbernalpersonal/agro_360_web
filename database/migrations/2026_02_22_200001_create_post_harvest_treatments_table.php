<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_harvest_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('agricultural_activities')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('phytosanitary_products')->onDelete('set null');

            $table->enum('application_type', [
                'copper_treatment',    // Tratamiento de cobre (mildiu)
                'sulfur_treatment',    // Tratamiento de azufre (oídio)
                'wound_sealing',       // Sellado/sellante de heridas de poda
                'foliar_application',  // Aplicación foliar otoñal general
                'trunk_treatment',     // Tratamiento de tronco (enfermedades madera)
                'other',
            ])->default('foliar_application');

            $table->decimal('treated_area_ha', 10, 4)->comment('Superficie tratada (ha)');
            $table->decimal('dose_per_hectare', 10, 3)->nullable()->comment('Dosis por hectárea');
            $table->string('dose_unit', 20)->nullable()->comment('Unidad de la dosis (kg/ha, L/ha, g/ha)');
            $table->decimal('water_volume_liters', 8, 2)->nullable()->comment('Volumen de agua/caldo (L)');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_harvest_treatments');
    }
};
