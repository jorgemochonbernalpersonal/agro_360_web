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
        // Drop old unique constraint only if it exists (may not exist on all envs)
        $indexes = array_column(Schema::getIndexes('grape_reception_batches'), 'name');
        if (in_array('grb_unique_winery_planting_campaign', $indexes)) {
            Schema::table('grape_reception_batches', function (Blueprint $table) {
                $table->dropUnique('grb_unique_winery_planting_campaign');
            });
        }

        // Drop foreign keys only if the columns exist as FKs
        $foreignKeys = array_column(Schema::getForeignKeys('grape_reception_batches'), 'name');

        Schema::table('grape_reception_batches', function (Blueprint $table) use ($foreignKeys) {
            // 2. Make plot_planting_id nullable
            $fkPlot = collect($foreignKeys)->first(fn ($k) => str_contains($k, 'plot_planting_id'));
            if ($fkPlot) {
                $table->dropForeign(['plot_planting_id']);
            }
            $table->unsignedBigInteger('plot_planting_id')->nullable()->change();
            if ($fkPlot) {
                $table->foreign('plot_planting_id')
                    ->references('id')
                    ->on('plot_plantings')
                    ->onDelete('set null');
            }

            // 3. Make campaign_id nullable
            $fkCampaign = collect($foreignKeys)->first(fn ($k) => str_contains($k, 'campaign_id'));
            if ($fkCampaign) {
                $table->dropForeign(['campaign_id']);
            }
            $table->unsignedBigInteger('campaign_id')->nullable()->change();
            if ($fkCampaign) {
                $table->foreign('campaign_id')
                    ->references('id')
                    ->on('campaigns')
                    ->onDelete('set null');
            }

            // NOTE: unique constraint (winery_id, viticulturist_id, vintage_year) skipped
            // because production already has duplicate rows — adding it would fail.
        });
    }

    public function down(): void
    {
        Schema::table('grape_reception_batches', function (Blueprint $table) {
            // unique constraint was not added in up(), nothing to drop here

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
