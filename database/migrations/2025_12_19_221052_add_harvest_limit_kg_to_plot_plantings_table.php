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
        Schema::table('plot_plantings', function (Blueprint $table) {
            $table->decimal('harvest_limit_kg', 10, 2)->nullable()->after('area_planted')
                ->comment('Límite máximo de cosecha permitido para esta plantación (kg). Por restricciones legales, comerciales o de planificación.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plot_plantings', function (Blueprint $table) {
            $table->dropColumn('harvest_limit_kg');
        });
    }
};
