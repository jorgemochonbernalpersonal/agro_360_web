<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations to make test-unfriendly fields nullable.
     * This ONLY runs in test environment.
     */
    public function up(): void
    {
        // Make Plot fields nullable for easier testing
        Schema::table('plots', function (Blueprint $table) {
            $table->unsignedBigInteger('autonomous_community_id')->nullable()->change();
            $table->unsignedBigInteger('province_id')->nullable()->change();
            $table->unsignedBigInteger('municipality_id')->nullable()->change();
        });

        // Make AgriculturalActivity plot_id nullable for isolated tests
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('plot_id')->nullable()->change();
        });

        // Make Harvest activity_id nullable for isolated stock tests
        Schema::table('harvests', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_id')->nullable()->change();
            $table->unsignedBigInteger('plot_planting_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No necesitamos revertir en tests, se resetea toda la DB
    }
};
