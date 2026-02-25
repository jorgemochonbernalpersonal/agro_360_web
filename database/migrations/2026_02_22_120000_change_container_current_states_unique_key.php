<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cambia la clave única de container_current_states de (container_id) a (container_id, harvest_id).
 *
 * Motivo: un contenedor puede tener múltiples cosechas simultáneamente.
 * Cada par (contenedor, cosecha) tiene su propio estado independiente.
 * HarvestStock (audit log) siempre fue correcto; ahora el snapshot también lo es.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('container_current_states', function (Blueprint $table) {
            // Eliminar la restricción unique que sólo cubre container_id
            $table->dropUnique(['container_id']);

            // Nueva restricción: un row por (contenedor, cosecha)
            $table->unique(['container_id', 'harvest_id'], 'uq_container_harvest');
        });
    }

    public function down(): void
    {
        Schema::table('container_current_states', function (Blueprint $table) {
            $table->dropUnique('uq_container_harvest');
            $table->unique('container_id');
        });
    }
};
