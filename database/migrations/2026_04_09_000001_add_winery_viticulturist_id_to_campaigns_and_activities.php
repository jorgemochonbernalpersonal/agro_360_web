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
        // Agregar winery_viticulturist_id a campaigns para vincular explícitamente
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('winery_viticulturist_id')
                ->nullable()
                ->after('viticulturist_id')
                ->constrained('winery_viticulturist')
                ->onDelete('cascade');
        });

        // Agregar winery_viticulturist_id a agricultural_activities
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->foreignId('winery_viticulturist_id')
                ->nullable()
                ->after('viticulturist_id')
                ->constrained('winery_viticulturist')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winery_viticulturist_id');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winery_viticulturist_id');
        });
    }
};
