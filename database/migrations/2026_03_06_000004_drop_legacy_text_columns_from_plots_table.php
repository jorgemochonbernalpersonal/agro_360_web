<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropColumn(['site_name', 'valley', 'soil_type', 'place']);
        });
    }

    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->string('site_name')->nullable()->after('site_id');
            $table->string('valley')->nullable()->after('valley_id');
            $table->string('soil_type')->nullable()->after('soil_type_id');
            $table->string('place')->nullable()->after('enclosure');
        });
    }
};
