<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->string('soil_type', 50)->nullable()->after('code_parcel');
            $table->string('orientation', 2)->nullable()->after('soil_type');
        });
    }

    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropColumn(['soil_type', 'orientation']);
        });
    }
};
