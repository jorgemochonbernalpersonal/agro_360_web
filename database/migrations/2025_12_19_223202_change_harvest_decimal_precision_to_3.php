<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cambiar precisión en harvests
        DB::statement('ALTER TABLE `harvests` MODIFY `total_weight` DECIMAL(10,3)');
        DB::statement('ALTER TABLE `harvests` MODIFY `yield_per_hectare` DECIMAL(10,3)');
        DB::statement('ALTER TABLE `harvests` MODIFY `baume_degree` DECIMAL(5,3)');
        DB::statement('ALTER TABLE `harvests` MODIFY `brix_degree` DECIMAL(5,3)');
        DB::statement('ALTER TABLE `harvests` MODIFY `acidity_level` DECIMAL(5,3)');
        DB::statement('ALTER TABLE `harvests` MODIFY `ph_level` DECIMAL(4,3)');
        DB::statement('ALTER TABLE `harvests` MODIFY `total_value` DECIMAL(12,3)');

        // Cambiar precisión en plot_plantings (harvest_limit_kg)
        DB::statement('ALTER TABLE `plot_plantings` MODIFY `harvest_limit_kg` DECIMAL(10,3)');

        // Cambiar precisión en estimated_yields
        DB::statement('ALTER TABLE `estimated_yields` MODIFY `estimated_yield_per_hectare` DECIMAL(10,3)');
        DB::statement('ALTER TABLE `estimated_yields` MODIFY `estimated_total_yield` DECIMAL(10,3)');
        DB::statement('ALTER TABLE `estimated_yields` MODIFY `actual_yield_per_hectare` DECIMAL(10,3)');
        DB::statement('ALTER TABLE `estimated_yields` MODIFY `actual_total_yield` DECIMAL(10,3)');

        // Cambiar precisión en harvest_containers
        DB::statement('ALTER TABLE `harvest_containers` MODIFY `weight` DECIMAL(10,3)');
        DB::statement('ALTER TABLE `harvest_containers` MODIFY `weight_per_unit` DECIMAL(10,3)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a 2 decimales en harvests
        DB::statement('ALTER TABLE `harvests` MODIFY `total_weight` DECIMAL(10,2)');
        DB::statement('ALTER TABLE `harvests` MODIFY `yield_per_hectare` DECIMAL(10,2)');
        DB::statement('ALTER TABLE `harvests` MODIFY `baume_degree` DECIMAL(5,2)');
        DB::statement('ALTER TABLE `harvests` MODIFY `brix_degree` DECIMAL(5,2)');
        DB::statement('ALTER TABLE `harvests` MODIFY `acidity_level` DECIMAL(5,2)');
        DB::statement('ALTER TABLE `harvests` MODIFY `ph_level` DECIMAL(4,2)');
        DB::statement('ALTER TABLE `harvests` MODIFY `total_value` DECIMAL(12,2)');

        // Revertir en plot_plantings
        DB::statement('ALTER TABLE `plot_plantings` MODIFY `harvest_limit_kg` DECIMAL(10,2)');

        // Revertir en estimated_yields
        DB::statement('ALTER TABLE `estimated_yields` MODIFY `estimated_yield_per_hectare` DECIMAL(10,2)');
        DB::statement('ALTER TABLE `estimated_yields` MODIFY `estimated_total_yield` DECIMAL(10,2)');
        DB::statement('ALTER TABLE `estimated_yields` MODIFY `actual_yield_per_hectare` DECIMAL(10,2)');
        DB::statement('ALTER TABLE `estimated_yields` MODIFY `actual_total_yield` DECIMAL(10,2)');

        // Revertir en harvest_containers
        DB::statement('ALTER TABLE `harvest_containers` MODIFY `weight` DECIMAL(10,2)');
        DB::statement('ALTER TABLE `harvest_containers` MODIFY `weight_per_unit` DECIMAL(10,2)');
    }
};
