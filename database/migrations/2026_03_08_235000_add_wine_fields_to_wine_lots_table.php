<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wine_lots', function (Blueprint $table) {
            // ── Crianza / elaboración básica ────────────────────────────
            $table->string('aging_type', 30)->nullable()->after('wine_type');   // joven, crianza, reserva…
            $table->unsignedSmallInteger('agingtime')->nullable()->after('aging_type'); // meses totales crianza
            $table->decimal('alcohol', 5, 2)->nullable()->after('agingtime');   // % vol

            // ── Analíticos ──────────────────────────────────────────────
            $table->decimal('residual_sugar', 6, 2)->nullable()->after('alcohol');
            $table->decimal('total_acidity', 5, 2)->nullable()->after('residual_sugar');
            $table->decimal('volatile_acidity', 5, 2)->nullable()->after('total_acidity');
            $table->decimal('ph', 4, 2)->nullable()->after('volatile_acidity');

            // ── Formato / comercial ─────────────────────────────────────
            $table->string('ean', 14)->nullable()->after('ph');
            $table->string('bottle_format', 20)->nullable()->after('ean');
            $table->unsignedTinyInteger('units_per_case')->nullable()->after('bottle_format');
            $table->decimal('cost_price', 10, 4)->nullable()->after('units_per_case');

            // ── Winemaking ──────────────────────────────────────────────
            $table->string('winemaker')->nullable()->after('cost_price');
            $table->string('harvest_method', 20)->nullable()->after('winemaker'); // manual, mechanic, mixed
            $table->string('fermentation_vessel')->nullable()->after('harvest_method');
            $table->string('oak_type')->nullable()->after('fermentation_vessel');
            $table->unsignedSmallInteger('oak_months')->nullable()->after('oak_type');

            // ── Viñedo ──────────────────────────────────────────────────
            $table->unsignedSmallInteger('vine_age')->nullable()->after('oak_months');
            $table->unsignedSmallInteger('altitude')->nullable()->after('vine_age');
            $table->string('soil_type')->nullable()->after('altitude');

            // ── Certificaciones ─────────────────────────────────────────
            $table->boolean('is_vegan')->default(false)->after('soil_type');
            $table->boolean('is_biodynamic')->default(false)->after('is_vegan');

            // ── Marketing / premios ─────────────────────────────────────
            $table->text('awards_notes')->nullable()->after('is_biodynamic');
            $table->unsignedInteger('production_quantity')->nullable()->after('awards_notes');
            $table->date('bottling_date')->nullable()->after('production_quantity');
            $table->date('release_date')->nullable()->after('bottling_date');
        });
    }

    public function down(): void
    {
        Schema::table('wine_lots', function (Blueprint $table) {
            $table->dropColumn([
                'aging_type', 'agingtime', 'alcohol',
                'residual_sugar', 'total_acidity', 'volatile_acidity', 'ph',
                'ean', 'bottle_format', 'units_per_case', 'cost_price',
                'winemaker', 'harvest_method', 'fermentation_vessel', 'oak_type', 'oak_months',
                'vine_age', 'altitude', 'soil_type',
                'is_vegan', 'is_biodynamic',
                'awards_notes', 'production_quantity', 'bottling_date', 'release_date',
            ]);
        });
    }
};
