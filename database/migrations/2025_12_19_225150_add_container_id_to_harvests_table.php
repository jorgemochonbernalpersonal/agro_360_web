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
        Schema::table('harvests', function (Blueprint $table) {
            $table->foreignId('container_id')->nullable()->after('plot_planting_id')
                ->constrained('harvest_containers')
                ->onDelete('set null')
                ->comment('Contenedor asignado a esta cosecha');
        });

        // Migrar datos existentes: asignar el primer contenedor de cada cosecha
        // Nota: Esto solo funciona si hay contenedores existentes vinculados a cosechas
        // Si no hay contenedores, container_id quedará NULL (lo cual es válido temporalmente)
        $containersByHarvest = DB::table('harvest_containers')
            ->whereNotNull('harvest_id')
            ->select('harvest_id', DB::raw('MIN(id) as first_container_id'))
            ->groupBy('harvest_id')
            ->get();

        foreach ($containersByHarvest as $row) {
            DB::table('harvests')
                ->where('id', $row->harvest_id)
                ->whereNull('container_id')
                ->update(['container_id' => $row->first_container_id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropForeign(['container_id']);
            $table->dropColumn('container_id');
        });
    }
};
