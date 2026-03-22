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
        Schema::table('post_harvest_treatments', function (Blueprint $table) {
            $table->unsignedSmallInteger('reentry_interval_hours')->nullable()->after('water_volume_liters')
                ->comment('Plazo de reentrada en horas — seguridad laboral PAC (0 = sin restricción)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_harvest_treatments', function (Blueprint $table) {
            $table->dropColumn('reentry_interval_hours');
        });
    }
};
