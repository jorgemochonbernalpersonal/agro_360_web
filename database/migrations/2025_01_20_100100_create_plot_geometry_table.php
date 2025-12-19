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
        Schema::create('plot_geometry', function (Blueprint $table) {
            $table->id();
            // MySQL geometry types - usar geometry para ambos campos
            $table->geometry('coordinates')->nullable();
            $table->geometry('centroid')->nullable();
            $table->timestamps();
        });
        
        // Crear índice espacial después (MySQL requiere sintaxis especial)
        // Solo si la columna no es null
        try {
            DB::statement('ALTER TABLE plot_geometry ADD SPATIAL INDEX idx_coordinates (coordinates)');
        } catch (\Exception $e) {
            // Si falla, continuar (puede que el índice ya exista o MySQL no soporte)
            \Log::warning('Could not create spatial index: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_geometry');
    }
};

