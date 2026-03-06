<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->decimal('limit_kg', 10, 2)->nullable()->after('maximum_yield_kg_ha')->comment('Límite de kg permitidos por campaña (cupo DO/bodega)');
            $table->decimal('cadastral_area', 10, 4)->nullable()->after('area')->comment('Superficie catastral registrada');
            $table->string('unit_of_measurement', 10)->nullable()->default('kg')->after('limit_kg')->comment('Unidad para limit_kg: kg, t');
            $table->smallInteger('plantation_year')->nullable()->after('degree_day_base')->comment('Año de plantación del viñedo');
            $table->boolean('is_organic')->default(false)->after('plantation_year')->comment('Producción ecológica certificada');
        });
    }

    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropColumn(['limit_kg', 'cadastral_area', 'unit_of_measurement', 'plantation_year', 'is_organic']);
        });
    }
};
