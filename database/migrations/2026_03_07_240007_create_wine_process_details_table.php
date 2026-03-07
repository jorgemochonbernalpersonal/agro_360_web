<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_process_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_id')->constrained('wines')->cascadeOnDelete();
            $table->foreignId('container_id')->nullable()->constrained('containers')->nullOnDelete();
            $table->enum('process_type', [
                'destemming_crushing', // Despalillado/estrujado
                'pressing',            // Prensado
                'settling',            // Desfangado
                'fermentation',        // Fermentación alcohólica
                'maceration',          // Maceración
                'malolactic',          // Fermentación maloláctica
                'aging',               // Crianza
                'racking',             // Trasiego
                'blending',            // Mezcla / coupage
                'fining',              // Clarificación
                'filtration',          // Filtrado
                'cold_stabilization',  // Estabilización tartárica
                'bottling',            // Embotellado
                'other',
            ])->default('fermentation');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('quantity', 12, 3)->nullable()->comment('Volumen/peso procesado');
            $table->foreignId('unit_of_measurement_id')->nullable()->constrained('units_of_measurement')->nullOnDelete();
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['wine_id', 'process_type']);
            $table->index(['wine_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_process_details');
    }
};
