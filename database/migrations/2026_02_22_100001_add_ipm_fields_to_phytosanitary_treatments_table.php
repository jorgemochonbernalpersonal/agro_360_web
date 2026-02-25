<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phytosanitary_treatments', function (Blueprint $table) {
            // Volumen de agua (caldo) — diferente a spray_volume que es total
            $table->decimal('water_volume_liters_ha', 8, 2)->nullable()->after('spray_volume')
                ->comment('Volumen de agua por hectárea (L/ha) — volumen de caldo');

            // Asesoramiento técnico
            $table->boolean('under_advisory')->default(false)->after('water_volume_liters_ha')
                ->comment('El tratamiento se realiza bajo asesoramiento técnico cualificado (RD 1311/2012)');
            $table->date('advisory_action_date')->nullable()->after('under_advisory')
                ->comment('Fecha de la recomendación del asesor técnico');

            // --- Gestión Integrada de Plagas (IPM) — obligatorio Producción Integrada ---
            $table->boolean('prior_non_chemical_methods')->default(false)->after('advisory_action_date')
                ->comment('IPM: Se aplicaron métodos no químicos previos al tratamiento');
            $table->boolean('plague_monitoring')->default(false)->after('prior_non_chemical_methods')
                ->comment('IPM: Se realizó seguimiento/monitoreo de la plaga antes de tratar');
            $table->boolean('manual_mechanical_control')->default(false)->after('plague_monitoring')
                ->comment('IPM: Se usó control manual o mecánico como alternativa o complemento');
            $table->boolean('biological_control')->default(false)->after('manual_mechanical_control')
                ->comment('IPM: Se usó control biológico (depredadores, parásitos, etc.)');
            $table->boolean('cultural_preventions')->default(false)->after('biological_control')
                ->comment('IPM: Se aplicaron medidas culturales preventivas (poda, eliminación de focos, etc.)');
        });
    }

    public function down(): void
    {
        Schema::table('phytosanitary_treatments', function (Blueprint $table) {
            $table->dropColumn([
                'water_volume_liters_ha',
                'under_advisory',
                'advisory_action_date',
                'prior_non_chemical_methods',
                'plague_monitoring',
                'manual_mechanical_control',
                'biological_control',
                'cultural_preventions',
            ]);
        });
    }
};
