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
        // Primero, eliminar registros con coordinates NULL (si los hay)
        DB::statement('DELETE FROM plot_geometry WHERE coordinates IS NULL');
        
        // Hacer la columna NOT NULL
        DB::statement('ALTER TABLE plot_geometry MODIFY coordinates GEOMETRY NOT NULL');
        
        // Ahora sí podemos añadir el índice espacial
        // Esto mejora significativamente el rendimiento de queries geoespaciales
        // como ST_Contains, ST_Intersects, ST_Distance, etc.
        DB::statement('ALTER TABLE plot_geometry ADD SPATIAL INDEX spatial_coordinates (coordinates)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índice espacial
        DB::statement('ALTER TABLE plot_geometry DROP INDEX spatial_coordinates');
    }
};
