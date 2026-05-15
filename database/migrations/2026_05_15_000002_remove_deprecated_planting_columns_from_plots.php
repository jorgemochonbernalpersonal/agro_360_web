<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            // Drop FK constraint first
            if (Schema::hasColumn('plots', 'training_system_id')) {
                $table->dropForeign(['training_system_id']);
            }
        });

        Schema::table('plots', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('plots', 'plantation_year')) {
                $columns[] = 'plantation_year';
            }
            if (Schema::hasColumn('plots', 'training_system_id')) {
                $columns[] = 'training_system_id';
            }
            if (Schema::hasColumn('plots', 'number_of_vines')) {
                $columns[] = 'number_of_vines';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            if (!Schema::hasColumn('plots', 'plantation_year')) {
                $table->smallInteger('plantation_year')->nullable()->after('planting_pattern');
            }
            if (!Schema::hasColumn('plots', 'training_system_id')) {
                $table->foreignId('training_system_id')->nullable()->after('planting_pattern')
                    ->constrained('training_systems')->nullOnDelete();
            }
            if (!Schema::hasColumn('plots', 'number_of_vines')) {
                $table->integer('number_of_vines')->nullable()->after('slope');
            }
        });
    }
};
