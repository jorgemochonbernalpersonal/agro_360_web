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
            $table->unsignedBigInteger('training_system_id')->nullable()->after('training_system');
            $table->foreign('training_system_id')->references('id')->on('training_systems')->nullOnDelete();
            $table->index('training_system_id', 'plot_plantings_training_system_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plot_plantings', function (Blueprint $table) {
            $table->dropForeign(['training_system_id']);
            $table->dropIndex('plot_plantings_training_system_idx');
            $table->dropColumn('training_system_id');
        });
    }
};


