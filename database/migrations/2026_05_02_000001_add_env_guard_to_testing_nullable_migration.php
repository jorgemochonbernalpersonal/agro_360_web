<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revert the nullable changes that 2099_99_99_999999_make_fields_nullable_for_testing
     * applied in production by mistake. Restore columns to NOT NULL.
     */
    public function up(): void
    {
        // Abort if there are NULL values — they need manual cleanup first
        $nullPlots = \DB::table('plots')->whereNull('autonomous_community_id')->orWhereNull('province_id')->orWhereNull('municipality_id')->count();
        $nullActivities = \DB::table('agricultural_activities')->whereNull('plot_id')->count();
        $nullHarvests = \DB::table('harvests')->whereNull('activity_id')->orWhereNull('plot_planting_id')->count();

        if ($nullPlots + $nullActivities + $nullHarvests > 0) {
            throw new \RuntimeException(
                "Cannot revert to NOT NULL: found {$nullPlots} plots, {$nullActivities} activities, {$nullHarvests} harvests with NULL values. Clean up manually first."
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

        Schema::table('harvests', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_id')->nullable(false)->change();
            $table->unsignedBigInteger('plot_planting_id')->nullable(false)->change();
        });
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

        Schema::table('harvests', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_id')->nullable()->change();
            $table->unsignedBigInteger('plot_planting_id')->nullable()->change();
        });
    }
};
