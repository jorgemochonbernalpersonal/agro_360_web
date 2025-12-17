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
        Schema::table('agricultural_activities', function (Blueprint $table) {
            // Agregar la columna como nullable inicialmente
            $table->integer('campaign_id')->nullable()->after('viticulturist_id');
            
            // Crear índice
            $table->index('campaign_id');
        });
        
        // Migrar datos existentes: crear campañas para cada año y asignar actividades
        // Esto se hará en un seeder o comando después de ejecutar las migraciones
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropIndex(['campaign_id']);
            $table->dropColumn('campaign_id');
        });
    }
};
