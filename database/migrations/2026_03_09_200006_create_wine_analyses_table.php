<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_id')->constrained('wines')->cascadeOnDelete();
            $table->foreignId('container_id')->nullable()->constrained('containers')->nullOnDelete();
            $table->date('analysis_date');
            $table->enum('analysis_type', [
                'own',       // Análisis propio / interno
                'external',  // Laboratorio externo
            ])->default('own');
            $table->string('laboratory_name')->nullable();

            // Parámetros fisicoquímicos (todos opcionales)
            $table->decimal('alcohol', 5, 2)->nullable()->comment('% vol');
            $table->decimal('residual_sugar', 6, 2)->nullable()->comment('g/L');
            $table->decimal('total_acidity', 5, 2)->nullable()->comment('g/L tartárico');
            $table->decimal('volatile_acidity', 5, 2)->nullable()->comment('g/L acético');
            $table->decimal('ph', 4, 2)->nullable();
            $table->decimal('so2_free', 6, 2)->nullable()->comment('mg/L SO₂ libre');
            $table->decimal('so2_total', 6, 2)->nullable()->comment('mg/L SO₂ total');
            $table->decimal('density', 7, 4)->nullable()->comment('g/cm³');
            $table->decimal('turbidity', 6, 2)->nullable()->comment('NTU');
            $table->decimal('color_intensity', 5, 3)->nullable();
            $table->decimal('malic_acid', 5, 2)->nullable()->comment('g/L - seguimiento FML');

            $table->string('document')->nullable()->comment('Ruta PDF del análisis');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['wine_id', 'analysis_date']);
            $table->index('container_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_analyses');
    }
};
