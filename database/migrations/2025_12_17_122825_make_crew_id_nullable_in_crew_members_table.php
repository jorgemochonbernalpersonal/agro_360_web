<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {
            // Hacer crew_id nullable para permitir trabajadores individuales
            $table->integer('crew_id')->nullable()->change();
            
            // Agregar índice compuesto para búsquedas eficientes
            $table->index(['viticulturist_id', 'crew_id'], 'idx_viticulturist_crew');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crew_members', function (Blueprint $table) {
            // Primero eliminar trabajadores individuales (crew_id = NULL)
            // antes de hacer la columna NOT NULL
            \DB::table('crew_members')->whereNull('crew_id')->delete();
            
            $table->dropIndex('idx_viticulturist_crew');
            $table->integer('crew_id')->nullable(false)->change();
        });
    }
};
