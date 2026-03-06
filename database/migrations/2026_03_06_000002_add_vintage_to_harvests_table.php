<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->unsignedSmallInteger('vintage')
                ->nullable()
                ->after('harvest_end_date')
                ->comment('Año de cosecha / añada (auto-calculado desde harvest_start_date)');

            $table->index('vintage');
        });

        // Backfill: extraer año de harvest_start_date en los registros existentes
        DB::statement('UPDATE harvests SET vintage = YEAR(harvest_start_date) WHERE vintage IS NULL AND harvest_start_date IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropIndex(['vintage']);
            $table->dropColumn('vintage');
        });
    }
};
