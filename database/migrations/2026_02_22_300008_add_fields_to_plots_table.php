<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->string('site_name')->nullable()->after('description')
                ->comment('Paraje / nombre del lugar');
            $table->string('valley')->nullable()->after('site_name')
                ->comment('Valle o zona geográfica');
            $table->string('code_parcel', 50)->nullable()->after('valley')
                ->comment('Código catastral de la parcela');
            $table->decimal('maximum_yield_kg_ha', 10, 2)->nullable()->after('code_parcel')
                ->comment('Rendimiento máximo histórico registrado (kg/ha)');
            $table->decimal('degree_day_base', 4, 1)->default(10)->after('maximum_yield_kg_ha')
                ->comment('Temperatura base para cálculo de grados-día (°C)');
        });
    }

    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropColumn(['site_name', 'valley', 'code_parcel', 'maximum_yield_kg_ha', 'degree_day_base']);
        });
    }
};
