<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade notebook_harvest_id a harvests para trazar la relación entre
 * la cosecha del cuaderno de campo (activity_id set, winery_id null)
 * y la recepción de bodega creada a partir de ella (winery_id set).
 *
 * Solo aplica al rol producer (flujo "Promover a recepción").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->foreignId('notebook_harvest_id')
                ->nullable()
                ->after('activity_id')
                ->constrained('harvests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropForeign(['notebook_harvest_id']);
            $table->dropColumn('notebook_harvest_id');
        });
    }
};
