<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero eliminar la foreign key constraint
        Schema::table('harvest_containers', function (Blueprint $table) {
            $table->dropForeign(['harvest_id']);
        });

        // Hacer harvest_id nullable
        DB::statement('ALTER TABLE `harvest_containers` MODIFY `harvest_id` BIGINT UNSIGNED NULL');

        // Recrear la foreign key con onDelete set null
        Schema::table('harvest_containers', function (Blueprint $table) {
            $table->foreign('harvest_id')
                ->references('id')
                ->on('harvests')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar foreign key
        Schema::table('harvest_containers', function (Blueprint $table) {
            $table->dropForeign(['harvest_id']);
        });

        // Hacer harvest_id NOT NULL (solo si no hay valores null)
        DB::statement('UPDATE `harvest_containers` SET `harvest_id` = 1 WHERE `harvest_id` IS NULL');
        DB::statement('ALTER TABLE `harvest_containers` MODIFY `harvest_id` BIGINT UNSIGNED NOT NULL');

        // Recrear foreign key con cascade
        Schema::table('harvest_containers', function (Blueprint $table) {
            $table->foreign('harvest_id')
                ->references('id')
                ->on('harvests')
                ->onDelete('cascade');
        });
    }
};
