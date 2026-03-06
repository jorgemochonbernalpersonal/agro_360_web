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
        Schema::table('plots', function (Blueprint $table) {
            $table->dropColumn(['limit_kg', 'unit_of_measurement']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->decimal('limit_kg', 10, 2)->nullable()->after('maximum_yield_kg_ha');
            $table->string('unit_of_measurement')->nullable()->after('limit_kg');
        });
    }
};
