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
            // Spectral bands (raw reflectances)
            $table->decimal('red_band', 6, 4)->nullable()->after('pixel_reliability')
                ->comment('Red band reflectance');
            $table->decimal('nir_band', 6, 4)->nullable()->after('red_band')
                ->comment('NIR band reflectance');
            $table->decimal('green_band', 6, 4)->nullable()->after('nir_band')
                ->comment('Green band reflectance');
            $table->decimal('blue_band', 6, 4)->nullable()->after('green_band')
                ->comment('Blue band reflectance');

            // Additional vegetation indices (from real spectral bands)
            $table->decimal('msr', 6, 4)->nullable()->after('ndre')
                ->comment('Modified Simple Ratio');
            $table->decimal('ci_green', 6, 4)->nullable()->after('msr')
                ->comment('Chlorophyll Index Green');
            $table->decimal('arvi', 6, 4)->nullable()->after('ci_green')
                ->comment('Atmospherically Resistant VI');

            // Official LAI data
            $table->decimal('fpar', 5, 3)->nullable()->after('lai')
                ->comment('Fraction of PAR (0-1)');
            $table->integer('lai_quality')->nullable()->after('fpar')
                ->comment('LAI quality flag from MODIS');

            // SMAP Soil Moisture
            $table->decimal('soil_moisture_surface_smap', 5, 2)->nullable()->after('soil_moisture')
                ->comment('SMAP surface soil moisture (%)');
            $table->decimal('soil_moisture_rootzone_smap', 5, 2)->nullable()->after('soil_moisture_surface_smap')
                ->comment('SMAP rootzone soil moisture (%)');

            // Official ET
            $table->decimal('et_nasa', 6, 2)->nullable()->after('et0')
                ->comment('NASA official ET (mm/day)');
            $table->decimal('pet_nasa', 6, 2)->nullable()->after('et_nasa')
                ->comment('NASA Potential ET (mm/day)');

            // Indexes for queries
            $table->index('fpar', 'idx_fpar');
            $table->index(['satellite', 'image_date'], 'idx_satellite_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plot_remote_sensing', function (Blueprint $table) {
            $table->dropIndex('idx_fpar');
            $table->dropIndex('idx_satellite_date');

            $table->dropColumn([
                'red_band',
                'nir_band',
                'green_band',
                'blue_band',
                'msr',
                'ci_green',
                'arvi',
                'fpar',
                'lai_quality',
                'soil_moisture_surface_smap',
                'soil_moisture_rootzone_smap',
                'et_nasa',
                'pet_nasa',
            ]);
        });
    }
};
