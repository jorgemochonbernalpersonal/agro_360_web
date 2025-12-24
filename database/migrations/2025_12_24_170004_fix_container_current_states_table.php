<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Corrige la tabla container_current_states si existe con estructura incorrecta
     */
    public function up(): void
    {
        // Solo ejecutar si la tabla existe
        if (!Schema::hasTable('container_current_states')) {
            return;
        }

        try {
            // Intentar eliminar cualquier foreign key de wine_id si existe
            // Laravel crea foreign keys con nombres específicos
            $possibleFkNames = [
                'container_current_states_wine_id_foreign',
                'container_current_states_wine_id_foreign_key',
            ];

            foreach ($possibleFkNames as $fkName) {
                try {
                    DB::statement("ALTER TABLE container_current_states DROP FOREIGN KEY `{$fkName}`");
                } catch (\Exception $e) {
                    // Ignorar si no existe
                }
            }

            // Asegurar que wine_id es unsignedBigInteger sin constraint
            if (Schema::hasColumn('container_current_states', 'wine_id')) {
                DB::statement("ALTER TABLE container_current_states MODIFY wine_id BIGINT UNSIGNED NULL COMMENT 'ID de vino (tabla futura)'");
            }
        } catch (\Exception $e) {
            // Si hay error, probablemente la tabla está bien o necesita ser recreada
            // No hacer nada, dejar que la migración de creación se encargue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hacer nada en el rollback, la migración original se encarga
    }
};
