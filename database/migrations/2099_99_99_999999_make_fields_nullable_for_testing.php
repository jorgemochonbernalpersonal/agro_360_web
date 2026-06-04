<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make test-unfriendly fields nullable.
     * This ONLY runs in test environment.
     *
     * NOTE: harvests.activity_id / plot_planting_id are already nullable in
     * production (grape receptions create harvests without activities), so
     * they are not included here.
     */
    public function up(): void
    {
        if (! app()->environment('testing')) {
            return;
        }

        Schema::table('plots', function (Blueprint $table) {
            $table->unsignedBigInteger('autonomous_community_id')->nullable()->change();
            $table->unsignedBigInteger('province_id')->nullable()->change();
            $table->unsignedBigInteger('municipality_id')->nullable()->change();
        });

        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('plot_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Tests reset the entire DB, no need to revert
    }
};
