<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_fermentation_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_id')->constrained('wines')->cascadeOnDelete();
            $table->foreignId('container_id')->constrained('containers')->restrictOnDelete();
            $table->datetime('control_date');

            // Parámetros de control (todos opcionales salvo fecha + contenedor)
            $table->decimal('temperature', 5, 2)->nullable()->comment('°C');
            $table->decimal('brix_degree', 5, 2)->nullable()->comment('°Brix');
            $table->decimal('baume_degree', 5, 2)->nullable()->comment('°Baumé');
            $table->decimal('density', 7, 4)->nullable()->comment('g/L');
            $table->decimal('ph', 4, 2)->nullable();
            $table->decimal('volatile_acidity', 5, 2)->nullable()->comment('g/L ác. acético');

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['wine_id', 'container_id']);
            $table->index(['wine_id', 'control_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_fermentation_controls');
    }
};
