<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cultural_works', function (Blueprint $table) {
            $table->string('defoliation_face', 10)->nullable()->after('productive_buds_per_hectare');
            $table->unsignedSmallInteger('topping_height_cm')->nullable()->after('defoliation_face');
        });
    }

    public function down(): void
    {
        Schema::table('cultural_works', function (Blueprint $table) {
            $table->dropColumn(['defoliation_face', 'topping_height_cm']);
        });
    }
};
