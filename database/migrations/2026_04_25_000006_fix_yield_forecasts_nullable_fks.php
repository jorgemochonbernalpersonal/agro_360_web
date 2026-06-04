<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make viticulturist_id, plot_planting_id, campaign_id nullable on winery_yield_forecasts.
 *
 * The API controller (YieldForecastController) already marks all three as
 * 'nullable|integer|exists:...', but the columns were created NOT NULL — causing
 * MySQL strict-mode error 1364 when the mobile client sends null for them.
 * The unique constraint references plot_planting_id + campaign_id, so we also
 * drop it before making the columns nullable and skip re-adding it (partial
 * NULLs in unique keys behave inconsistently across MySQL versions).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop unique constraint if it exists
        $indexes = array_column(Schema::getIndexes('winery_yield_forecasts'), 'name');
        if (in_array('wyf_unique_winery_planting_campaign', $indexes)) {
            Schema::table('winery_yield_forecasts', function (Blueprint $table) {
                $table->dropUnique('wyf_unique_winery_planting_campaign');
            });
        }

        $foreignKeys = array_column(Schema::getForeignKeys('winery_yield_forecasts'), 'name');

        Schema::table('winery_yield_forecasts', function (Blueprint $table) use ($foreignKeys) {
            // viticulturist_id
            $fk = collect($foreignKeys)->first(fn ($k) => str_contains($k, 'viticulturist_id'));
            if ($fk) {
                $table->dropForeign(['viticulturist_id']);
            }
            $table->unsignedBigInteger('viticulturist_id')->nullable()->change();
            if ($fk) {
                $table->foreign('viticulturist_id')->references('id')->on('users')->onDelete('set null');
            }

            // plot_planting_id
            $fk = collect($foreignKeys)->first(fn ($k) => str_contains($k, 'plot_planting_id'));
            if ($fk) {
                $table->dropForeign(['plot_planting_id']);
            }
            $table->unsignedBigInteger('plot_planting_id')->nullable()->change();
            if ($fk) {
                $table->foreign('plot_planting_id')->references('id')->on('plot_plantings')->onDelete('set null');
            }

            // campaign_id
            $fk = collect($foreignKeys)->first(fn ($k) => str_contains($k, 'campaign_id'));
            if ($fk) {
                $table->dropForeign(['campaign_id']);
            }
            $table->unsignedBigInteger('campaign_id')->nullable()->change();
            if ($fk) {
                $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('winery_yield_forecasts', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->unsignedBigInteger('campaign_id')->nullable(false)->change();
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');

            $table->dropForeign(['plot_planting_id']);
            $table->unsignedBigInteger('plot_planting_id')->nullable(false)->change();
            $table->foreign('plot_planting_id')->references('id')->on('plot_plantings')->onDelete('cascade');

            $table->dropForeign(['viticulturist_id']);
            $table->unsignedBigInteger('viticulturist_id')->nullable(false)->change();
            $table->foreign('viticulturist_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['winery_id', 'plot_planting_id', 'campaign_id'], 'wyf_unique_winery_planting_campaign');
        });
    }
};
