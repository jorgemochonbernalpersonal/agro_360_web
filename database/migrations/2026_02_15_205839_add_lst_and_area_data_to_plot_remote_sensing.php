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
            // LST (Land Surface Temperature) data
            $table->decimal('lst_day', 6, 2)->nullable()->after('temperature_max')
                ->comment('Land Surface Temperature Day (°C) from MODIS');
            $table->decimal('lst_night', 6, 2)->nullable()->after('lst_day')
                ->comment('Land Surface Temperature Night (°C)');
            $table->decimal('lst_diff', 6, 2)->nullable()->after('lst_night')
                ->comment('Day-Night Temperature Difference (DTR)');

            // CWSI (Crop Water Stress Index) - improved with LST
            $table->decimal('cwsi', 5, 3)->nullable()->after('lst_diff')
                ->comment('Crop Water Stress Index (0-1, higher = more stress)');

            // Area statistics (when using area request instead of point)
            $table->json('area_statistics')->nullable()->after('cwsi')
                ->comment('Statistics from area request: min, max, mean, stddev, percentiles');

            // Data source tracking
            $table->string('data_source', 50)->default('point')->after('image_source')
                ->comment('point, area, or timeseries');
            $table->string('satellite', 20)->default('MODIS')->after('data_source')
                ->comment('MODIS, VIIRS, or other');

            // Quality flags
            $table->integer('pixel_reliability')->nullable()->after('satellite')
                ->comment('Pixel reliability flag (0=good, 1=marginal, 2-3=poor)');

            // Indexes
            $table->index('lst_day', 'idx_lst_day');
            $table->index('cwsi', 'idx_cwsi');
            $table->index(['data_source', 'satellite'], 'idx_data_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plot_remote_sensing', function (Blueprint $table) {
            $table->dropIndex('idx_lst_day');
            $table->dropIndex('idx_cwsi');
            $table->dropIndex('idx_data_source');

            $table->dropColumn([
                'lst_day',
                'lst_night',
                'lst_diff',
                'cwsi',
                'area_statistics',
                'data_source',
                'satellite',
                'pixel_reliability',
            ]);
        });
    }
};
