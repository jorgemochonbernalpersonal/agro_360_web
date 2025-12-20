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
        // Eliminar constraint único existente si existe
        try {
            Schema::table('winery_viticulturist', function (Blueprint $table) {
                $table->dropUnique('winery_viticulturist_winery_id_viticulturist_id_unique');
            });
        } catch (\Exception $e) {
            // Si no existe, continuar
        }
        
        // Para MySQL/MariaDB: índice único normal
        // MySQL permite múltiples NULLs en índices únicos, así que esto funciona
        // cuando winery_id es NULL, permite múltiples filas con el mismo viticulturist_id
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            $table->unique(['winery_id', 'viticulturist_id'], 'winery_viticulturist_winery_id_viticulturist_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('winery_viticulturist', function (Blueprint $table) {
                $table->dropUnique('winery_viticulturist_winery_id_viticulturist_id_unique');
            });
        } catch (\Exception $e) {
            // Si no existe, continuar
        }
        
        // Restaurar constraint único original
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            $table->unique(['winery_id', 'viticulturist_id'], 'winery_viticulturist_winery_id_viticulturist_id_unique');
        });
    }
};
