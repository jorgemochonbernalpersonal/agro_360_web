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
        Schema::table('phytosanitary_products', function (Blueprint $table) {
            $table->unsignedInteger('safety_interval_days')->nullable()->after('active_ingredient')->comment('Plazo de seguridad en días');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phytosanitary_products', function (Blueprint $table) {
            $table->dropColumn('safety_interval_days');
        });
    }
};
