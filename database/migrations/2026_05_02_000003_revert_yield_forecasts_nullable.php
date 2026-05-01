<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revert winery_yield_forecasts nullable changes.
     * Delete corrupt rows with NULL FK values, then restore NOT NULL.
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
            $table->unsignedBigInteger('viticulturist_id')->nullable(false)->change();
            $table->unsignedBigInteger('plot_planting_id')->nullable(false)->change();
            $table->unsignedBigInteger('campaign_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations (make fields nullable again).
     */
    public function down(): void
    {
        Schema::table('winery_yield_forecasts', function (Blueprint $table) {
            $table->unsignedBigInteger('viticulturist_id')->nullable()->change();
            $table->unsignedBigInteger('plot_planting_id')->nullable()->change();
            $table->unsignedBigInteger('campaign_id')->nullable()->change();
        });
    }
};
