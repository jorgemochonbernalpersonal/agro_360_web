<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revert the nullable changes that 2099_99_99_999999_make_fields_nullable_for_testing
     * applied in production by mistake. Restore columns to NOT NULL where safe.
     *
     * NOTE: harvests.activity_id and harvests.plot_planting_id are intentionally
     * left nullable — grape receptions create harvests without an activity or
     * specific planting, so NULL is a valid state for those columns.
     */
    public function up(): void
    {
        // Safety check: abort if there are NULL values in columns we are reverting
        $nullPlots = \DB::table('plots')->whereNull('autonomous_community_id')->orWhereNull('province_id')->orWhereNull('municipality_id')->count();
        $nullActivities = \DB::table('agricultural_activities')->whereNull('plot_id')->count();

        if ($nullPlots + $nullActivities > 0) {
            throw new \RuntimeException(
                "Cannot revert to NOT NULL: found {$nullPlots} plots, {$nullActivities} activities with NULL values. Clean up manually first."
            );
        }

        Schema::table('plots', function (Blueprint $table) {
            $table->unsignedBigInteger('autonomous_community_id')->nullable(false)->change();
            $table->unsignedBigInteger('province_id')->nullable(false)->change();
            $table->unsignedBigInteger('municipality_id')->nullable(false)->change();
        });

        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('plot_id')->nullable(false)->change();
        });

        // harvests.activity_id and harvests.plot_planting_id stay nullable:
        // grape receptions create harvests without an agricultural activity.
    }

    /**
     * Reverse the migrations (make fields nullable again).
     */
    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->unsignedBigInteger('autonomous_community_id')->nullable()->change();
            $table->unsignedBigInteger('province_id')->nullable()->change();
            $table->unsignedBigInteger('municipality_id')->nullable()->change();
        });

        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('plot_id')->nullable()->change();
        });
    }
};
