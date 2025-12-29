<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrar datos de container_states a container_current_states
     * Nota: container_states apunta a harvest_containers (obsoleta)
     * container_current_states apunta a containers (nueva)
     * 
     * Esta migración intenta mapear datos a través de harvests que tienen container_id
     */
    public function up(): void
    {
        // Solo migrar si existe la tabla container_states
        if (!Schema::hasTable('container_states')) {
            return;
        }

        // Migrar datos: buscar harvests que tienen container_id en containers
        // y que tienen datos en container_states a través de harvest_containers
        $statesToMigrate = DB::table('container_states')
            ->join('harvest_containers', 'container_states.container_id', '=', 'harvest_containers.id')
            ->join('harvests', 'harvest_containers.harvest_id', '=', 'harvests.id')
            ->join('containers', 'harvests.container_id', '=', 'containers.id')
            ->select(
                'containers.id as container_id',
                'container_states.harvest_id',
                'container_states.available_qty',
                'container_states.reserved_qty',
                'container_states.sold_qty',
                'container_states.location',
                'container_states.last_movement_at',
                'container_states.last_movement_by',
                'container_states.has_subproducts'
            )
            ->get();

        foreach ($statesToMigrate as $state) {
            // Actualizar o crear container_current_state
            DB::table('container_current_states')->updateOrInsert(
                ['container_id' => $state->container_id],
                [
                    'harvest_id' => $state->harvest_id,
                    'current_quantity' => $state->available_qty + $state->reserved_qty + $state->sold_qty,
                    'available_qty' => $state->available_qty ?? 0,
                    'reserved_qty' => $state->reserved_qty ?? 0,
                    'sold_qty' => $state->sold_qty ?? 0,
                    'location' => $state->location,
                    'last_movement_at' => $state->last_movement_at,
                    'last_movement_by' => $state->last_movement_by,
                    'has_subproducts' => $state->has_subproducts ?? false,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir la migración de datos
        // Los datos ya están en container_current_states
    }
};
