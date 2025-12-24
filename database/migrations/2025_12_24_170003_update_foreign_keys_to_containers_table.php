<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Actualiza las foreign keys de harvest_stocks y harvests para que apunten a containers
     * en lugar de harvest_containers.
     */
    public function up(): void
    {
        // Actualizar foreign key en harvest_stocks
        Schema::table('harvest_stocks', function (Blueprint $table) {
            // Eliminar la foreign key antigua
            $table->dropForeign(['container_id']);
        });

        Schema::table('harvest_stocks', function (Blueprint $table) {
            // Agregar nueva foreign key apuntando a containers
            $table->foreign('container_id')
                ->references('id')
                ->on('containers')
                ->onDelete('set null');
        });

        // Actualizar foreign key en harvests
        Schema::table('harvests', function (Blueprint $table) {
            // Eliminar la foreign key antigua
            $table->dropForeign(['container_id']);
        });

        Schema::table('harvests', function (Blueprint $table) {
            // Agregar nueva foreign key apuntando a containers
            $table->foreign('container_id')
                ->references('id')
                ->on('containers')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir foreign key en harvest_stocks
        Schema::table('harvest_stocks', function (Blueprint $table) {
            $table->dropForeign(['container_id']);
        });

        Schema::table('harvest_stocks', function (Blueprint $table) {
            $table->foreign('container_id')
                ->references('id')
                ->on('harvest_containers')
                ->onDelete('set null');
        });

        // Revertir foreign key en harvests
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropForeign(['container_id']);
        });

        Schema::table('harvests', function (Blueprint $table) {
            $table->foreign('container_id')
                ->references('id')
                ->on('harvest_containers')
                ->onDelete('set null');
        });
    }
};

