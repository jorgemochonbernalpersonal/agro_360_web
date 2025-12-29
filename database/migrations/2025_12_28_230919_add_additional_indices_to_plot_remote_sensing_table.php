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
        Schema::table('plot_remote_sensing', function (Blueprint $table) {
            // Datos meteorológicos
            $table->decimal('temperature', 5, 2)->nullable()->comment('°C');
            $table->decimal('temperature_min', 5, 2)->nullable()->comment('°C');
            $table->decimal('temperature_max', 5, 2)->nullable()->comment('°C');
            $table->decimal('precipitation', 6, 2)->nullable()->comment('mm');
            $table->decimal('humidity', 5, 2)->nullable()->comment('%');
            $table->decimal('wind_speed', 5, 2)->nullable()->comment('km/h');
            
            // Humedad del suelo
            $table->decimal('soil_moisture', 5, 2)->nullable()->comment('% volumétrico');
            $table->decimal('soil_temperature', 5, 2)->nullable()->comment('°C');
            
            // Radiación solar
            $table->decimal('solar_radiation', 8, 2)->nullable()->comment('W/m²');
            $table->decimal('et0', 5, 2)->nullable()->comment('mm/día - Evapotranspiración referencia');
            $table->decimal('sunshine_hours', 4, 1)->nullable()->comment('horas');
            
            // Estado hídrico
            $table->string('water_stress_status', 20)->nullable()->comment('optimal, mild, moderate, severe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plot_remote_sensing', function (Blueprint $table) {
            $table->dropColumn([
                'temperature', 'temperature_min', 'temperature_max',
                'precipitation', 'humidity', 'wind_speed',
                'soil_moisture', 'soil_temperature',
                'solar_radiation', 'et0', 'sunshine_hours',
                'water_stress_status',
            ]);
        });
    }
};
