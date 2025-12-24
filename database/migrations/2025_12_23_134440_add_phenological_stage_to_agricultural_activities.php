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
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->string('phenological_stage', 50)->nullable()->after('activity_type')
                ->comment('Estadio fenológico del cultivo (brotación, floración, envero, maduración, etc.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->dropColumn('phenological_stage');
        });
    }
};
