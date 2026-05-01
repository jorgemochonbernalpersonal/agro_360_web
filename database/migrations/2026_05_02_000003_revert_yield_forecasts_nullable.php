<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revert winery_yield_forecasts nullable changes.
     * Delete corrupt rows with NULL FK values, change FK action from SET NULL
     * to CASCADE, then restore NOT NULL.
     */
    public function up(): void
    {
        // Delete corrupt rows where any required FK is NULL
        DB::table('winery_yield_forecasts')
            ->whereNull('viticulturist_id')
            ->orWhereNull('plot_planting_id')
            ->orWhereNull('campaign_id')
            ->delete();

        Schema::table('winery_yield_forecasts', function (Blueprint $table) {
            // Drop existing SET NULL foreign keys
            $table->dropForeign(['viticulturist_id']);
            $table->dropForeign(['plot_planting_id']);
            $table->dropForeign(['campaign_id']);

            // Make columns NOT NULL
            $table->unsignedBigInteger('viticulturist_id')->nullable(false)->change();
            $table->unsignedBigInteger('plot_planting_id')->nullable(false)->change();
            $table->unsignedBigInteger('campaign_id')->nullable(false)->change();

            // Re-add foreign keys with CASCADE (compatible with NOT NULL)
            $table->foreign('viticulturist_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plot_planting_id')->references('id')->on('plot_plantings')->onDelete('cascade');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations (make fields nullable again with SET NULL).
     */
    public function down(): void
    {
        Schema::table('winery_yield_forecasts', function (Blueprint $table) {
            $table->dropForeign(['viticulturist_id']);
            $table->dropForeign(['plot_planting_id']);
            $table->dropForeign(['campaign_id']);

            $table->unsignedBigInteger('viticulturist_id')->nullable()->change();
            $table->unsignedBigInteger('plot_planting_id')->nullable()->change();
            $table->unsignedBigInteger('campaign_id')->nullable()->change();

            $table->foreign('viticulturist_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('plot_planting_id')->references('id')->on('plot_plantings')->onDelete('set null');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('set null');
        });
    }
};
