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
        Schema::table('plot_plantings', function (Blueprint $table) {
            $table->dropColumn('planting_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plot_plantings', function (Blueprint $table) {
            $table->date('planting_date')->nullable()->after('planting_year');
        });
    }
};
