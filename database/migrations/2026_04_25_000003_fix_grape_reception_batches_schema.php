<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix grape_reception_batches to match GrapeReceptionController logic.
 *
 * The original migration created plot_planting_id and campaign_id as NOT NULL,
 * but the controller groups batches by (winery_id, viticulturist_id, vintage_year)
 * and never provides these two columns — causing a MySQL strict-mode 1364 error
 * on every firstOrCreate() call.
 *
 * Changes:
 *   1. Drop old unique constraint (references plot_planting_id + campaign_id).
 *   2. Make plot_planting_id nullable (drop FK, change column, re-add nullable FK).
 *   3. Make campaign_id nullable (drop FK, change column, re-add nullable FK).
 *   4. Add unique constraint on (winery_id, viticulturist_id, vintage_year) to
 *      match the controller's firstOrCreate() key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grape_reception_batches', function (Blueprint $table) {
            // 1. Drop old unique constraint
            $table->dropUnique('grb_unique_winery_planting_campaign');

            // 2. Make plot_planting_id nullable
            $table->dropForeign(['plot_planting_id']);
            $table->unsignedBigInteger('plot_planting_id')->nullable()->change();
            $table->foreign('plot_planting_id')
                ->references('id')
                ->on('plot_plantings')
                ->onDelete('set null');

            // 3. Make campaign_id nullable
            $table->dropForeign(['campaign_id']);
            $table->unsignedBigInteger('campaign_id')->nullable()->change();
            $table->foreign('campaign_id')
                ->references('id')
                ->on('campaigns')
                ->onDelete('set null');

            // 4. New unique constraint matching the controller's grouping key
            $table->unique(['winery_id', 'viticulturist_id', 'vintage_year'], 'grb_unique_winery_viticulturist_year');
        });
    }

    public function down(): void
    {
        Schema::table('grape_reception_batches', function (Blueprint $table) {
            $table->dropUnique('grb_unique_winery_viticulturist_year');

            $table->dropForeign(['campaign_id']);
            $table->unsignedBigInteger('campaign_id')->nullable(false)->change();
            $table->foreign('campaign_id')
                ->references('id')
                ->on('campaigns')
                ->onDelete('cascade');

            $table->dropForeign(['plot_planting_id']);
            $table->unsignedBigInteger('plot_planting_id')->nullable(false)->change();
            $table->foreign('plot_planting_id')
                ->references('id')
                ->on('plot_plantings')
                ->onDelete('cascade');

            $table->unique(['winery_id', 'plot_planting_id', 'campaign_id'], 'grb_unique_winery_planting_campaign');
        });
    }
};
