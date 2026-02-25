<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimated_yields', function (Blueprint $table) {
            // --- Campos de muestreo de campo ---
            $table->integer('thumbs_per_vine')->nullable()->after('notes')
                ->comment('Yemas/brotes por cepa (dato de poda)');
            $table->decimal('bunches_per_plant', 8, 2)->nullable()->after('thumbs_per_vine')
                ->comment('Racimos contados por planta (muestreo de campo)');
            $table->decimal('bunch_weight_grams', 8, 2)->nullable()->after('bunches_per_plant')
                ->comment('Peso medio del racimo en gramos (muestreo)');
            $table->integer('total_plants_sampled')->nullable()->after('bunch_weight_grams')
                ->comment('Número de plantas muestreadas');
            $table->decimal('sampling_area_pct', 5, 2)->nullable()->after('total_plants_sampled')
                ->comment('Porcentaje del viñedo muestreado (0-100)');
            $table->decimal('health_percentage', 5, 2)->nullable()->after('sampling_area_pct')
                ->comment('Porcentaje de racimos sanos (0-100)');
            $table->decimal('potential_alcohol', 5, 2)->nullable()->after('health_percentage')
                ->comment('Grado alcohólico probable (%)');
            $table->year('vintage')->nullable()->after('potential_alcohol')
                ->comment('Añada estimada');
            $table->decimal('auto_calculated_yield', 10, 2)->nullable()->after('vintage')
                ->comment('Rendimiento calculado automáticamente desde los datos de muestreo (kg)');

            // --- Multi-ronda: varias estimaciones por plantación/campaña ---
            $table->tinyInteger('estimation_round')->default(1)->after('auto_calculated_yield')
                ->comment('Ronda de estimación: 1=pre-envero, 2=envero, 3=pre-vendimia, 4=revisión');

            // Eliminar constraint único anterior y crear uno nuevo con estimation_round
            $table->dropUnique('unique_plot_planting_campaign');
            $table->unique(['plot_planting_id', 'campaign_id', 'estimation_round'], 'unique_planting_campaign_round');
        });
    }

    public function down(): void
    {
        Schema::table('estimated_yields', function (Blueprint $table) {
            $table->dropUnique('unique_planting_campaign_round');
            $table->unique(['plot_planting_id', 'campaign_id'], 'unique_plot_planting_campaign');

            $table->dropColumn([
                'thumbs_per_vine',
                'bunches_per_plant',
                'bunch_weight_grams',
                'total_plants_sampled',
                'sampling_area_pct',
                'health_percentage',
                'potential_alcohol',
                'vintage',
                'auto_calculated_yield',
                'estimation_round',
            ]);
        });
    }
};
