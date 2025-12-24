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
        Schema::table('phytosanitary_treatments', function (Blueprint $table) {
            // Campo 1: Justificación del tratamiento (plaga/enfermedad detectada)
            $table->text('treatment_justification')->nullable()->after('area_treated')
                ->comment('Justificación del tratamiento: plaga o enfermedad detectada (obligatorio PAC)');
            
            // Campo 2: Número ROPO del aplicador
            $table->string('applicator_ropo_number', 50)->nullable()->after('treatment_justification')
                ->comment('Número de Registro Oficial de Productores y Operadores del aplicador');
            
            // Campo 3: Plazo de reentrada (días sin acceso tras aplicación)
            $table->integer('reentry_period_days')->nullable()->after('applicator_ropo_number')
                ->comment('Días sin acceso a la parcela tras aplicación (obligatorio PAC)');
            
            // Campo 4: Volumen de caldo aplicado (litros totales)
            $table->decimal('spray_volume', 10, 2)->nullable()->after('reentry_period_days')
                ->comment('Volumen total de caldo aplicado en litros');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phytosanitary_treatments', function (Blueprint $table) {
            $table->dropColumn([
                'treatment_justification',
                'applicator_ropo_number',
                'reentry_period_days',
                'spray_volume',
            ]);
        });
    }
};
