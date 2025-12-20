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
        // Función helper para verificar si el índice existe
        $indexExists = function($table, $index) {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'pgsql') {
                $result = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $index]);
                return !empty($result);
            }
            // Para MySQL/MariaDB
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
            return !empty($result);
        };

        Schema::table('taxes', function (Blueprint $table) use ($indexExists) {
            // Verificar y eliminar el índice único de code si existe
            // Laravel genera el nombre 'taxes_code_unique' para un unique en 'code'
            if ($indexExists('taxes', 'taxes_code_unique')) {
                $table->dropUnique(['code']);
            }
            
            // Verificar si el índice compuesto ya existe antes de crearlo
            if (!$indexExists('taxes', 'tax_code_rate_region_unique')) {
                // Agregar índice único compuesto (code, rate, region)
                $table->unique(['code', 'rate', 'region'], 'tax_code_rate_region_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Función helper para verificar si el índice existe
        $indexExists = function($table, $index) {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'pgsql') {
                $result = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $index]);
                return !empty($result);
            }
            // Para MySQL/MariaDB
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
            return !empty($result);
        };

        Schema::table('taxes', function (Blueprint $table) use ($indexExists) {
            // Eliminar el índice único compuesto si existe
            if ($indexExists('taxes', 'tax_code_rate_region_unique')) {
                $table->dropUnique('tax_code_rate_region_unique');
            }
            
            // Restaurar el índice único de code (solo si no existe)
            if (!$indexExists('taxes', 'taxes_code_unique')) {
                $table->unique('code');
            }
        });
    }
};
