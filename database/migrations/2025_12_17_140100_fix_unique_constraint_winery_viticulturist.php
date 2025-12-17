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
        // En PostgreSQL, podemos usar un índice parcial único
        // que solo se aplica cuando winery_id IS NOT NULL
        // Esto permite múltiples filas con winery_id = NULL y el mismo viticulturist_id
        
        // Primero eliminar el constraint único existente
        DB::statement('ALTER TABLE winery_viticulturist DROP CONSTRAINT IF EXISTS winery_viticulturist_winery_id_viticulturist_id_unique');
        
        // Crear índice parcial único que solo se aplica cuando winery_id no es NULL
        // Esto permite múltiples relaciones sin winery para el mismo viticultor
        DB::statement('
            CREATE UNIQUE INDEX winery_viticulturist_unique_partial 
            ON winery_viticulturist (winery_id, viticulturist_id) 
            WHERE winery_id IS NOT NULL
        ');
        
        // También crear un índice único parcial para viticultores sin winery
        // pero con parent_viticulturist_id para evitar duplicados
        DB::statement('
            CREATE UNIQUE INDEX winery_viticulturist_unique_no_winery 
            ON winery_viticulturist (viticulturist_id, parent_viticulturist_id, source) 
            WHERE winery_id IS NULL AND parent_viticulturist_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices parciales
        DB::statement('DROP INDEX IF EXISTS winery_viticulturist_unique_partial');
        DB::statement('DROP INDEX IF EXISTS winery_viticulturist_unique_no_winery');
        
        // Restaurar constraint único original
        Schema::table('winery_viticulturist', function (Blueprint $table) {
            $table->unique(['winery_id', 'viticulturist_id'], 'winery_viticulturist_winery_id_viticulturist_id_unique');
        });
    }
};

