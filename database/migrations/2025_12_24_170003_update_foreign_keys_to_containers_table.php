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
        // PASO 1: Limpiar registros huérfanos en harvest_stocks
        // Establecer a NULL los container_id que no existen en la tabla containers
        if (Schema::hasTable('harvest_stocks')) {
            $existingContainerIds = DB::table('containers')->pluck('id')->toArray();
            
            if (!empty($existingContainerIds)) {
                // Establecer a NULL los container_id que no existen en containers
                DB::table('harvest_stocks')
                    ->whereNotNull('container_id')
                    ->whereNotIn('container_id', $existingContainerIds)
                    ->update(['container_id' => null]);
            } else {
                // Si no hay containers, establecer todos a NULL
                DB::table('harvest_stocks')
                    ->whereNotNull('container_id')
                    ->update(['container_id' => null]);
            }
        }

        // PASO 2: Limpiar registros huérfanos en harvests
        if (Schema::hasTable('harvests')) {
            $existingContainerIds = DB::table('containers')->pluck('id')->toArray();
            
            if (!empty($existingContainerIds)) {
                // Establecer a NULL los container_id que no existen en containers
                DB::table('harvests')
                    ->whereNotNull('container_id')
                    ->whereNotIn('container_id', $existingContainerIds)
                    ->update(['container_id' => null]);
            } else {
                // Si no hay containers, establecer todos a NULL
                DB::table('harvests')
                    ->whereNotNull('container_id')
                    ->update(['container_id' => null]);
            }
        }

        // PASO 3: Actualizar foreign key en harvest_stocks
        if (Schema::hasTable('harvest_stocks')) {
            // Verificar si existe la foreign key antes de eliminarla
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'harvest_stocks' 
                AND COLUMN_NAME = 'container_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (!empty($foreignKeys)) {
                Schema::table('harvest_stocks', function (Blueprint $table) {
                    // Intentar eliminar la foreign key antigua (puede tener diferentes nombres)
                    try {
                        $table->dropForeign(['container_id']);
                    } catch (\Exception $e) {
                        // Si falla, intentar con el nombre específico
                        $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
                        DB::statement("ALTER TABLE `harvest_stocks` DROP FOREIGN KEY `{$constraintName}`");
                    }
                });
            }

            Schema::table('harvest_stocks', function (Blueprint $table) {
                // Agregar nueva foreign key apuntando a containers
                $table->foreign('container_id')
                    ->references('id')
                    ->on('containers')
                    ->onDelete('set null');
            });
        }

        // PASO 4: Actualizar foreign key en harvests
        if (Schema::hasTable('harvests')) {
            // Verificar si existe la foreign key antes de eliminarla
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'harvests' 
                AND COLUMN_NAME = 'container_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (!empty($foreignKeys)) {
                Schema::table('harvests', function (Blueprint $table) {
                    // Intentar eliminar la foreign key antigua
                    try {
                        $table->dropForeign(['container_id']);
                    } catch (\Exception $e) {
                        // Si falla, intentar con el nombre específico
                        $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
                        DB::statement("ALTER TABLE `harvests` DROP FOREIGN KEY `{$constraintName}`");
                    }
                });
            }

            Schema::table('harvests', function (Blueprint $table) {
                // Agregar nueva foreign key apuntando a containers
                $table->foreign('container_id')
                    ->references('id')
                    ->on('containers')
                    ->onDelete('set null');
            });
        }
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

