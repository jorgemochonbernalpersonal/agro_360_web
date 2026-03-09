<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_losses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_id')->constrained('wines')->cascadeOnDelete();
            $table->foreignId('container_id')->nullable()->constrained('containers')->nullOnDelete();
            $table->enum('loss_type', [
                'evaporation',  // Evaporación / Merma natural
                'filtration',   // Filtración
                'sampling',     // Muestreo / Analítica
                'spillage',     // Derrame accidental
                'other',        // Otro
            ])->default('evaporation');
            $table->decimal('quantity', 12, 3);
            $table->foreignId('unit_of_measurement_id')->constrained('units_of_measurement');
            $table->date('loss_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('wine_id');
            $table->index('container_id');
            $table->index('loss_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_losses');
    }
};
