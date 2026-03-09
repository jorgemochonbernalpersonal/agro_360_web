<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_id')->constrained('wines')->cascadeOnDelete();
            $table->foreignId('from_container_id')->nullable()->constrained('containers')->nullOnDelete();
            $table->foreignId('to_container_id')->constrained('containers')->restrictOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->foreignId('unit_of_measurement_id')->constrained('units_of_measurement');
            $table->enum('transfer_type', [
                'racking',   // Trasiego
                'blending',  // Mezcla / Coupage
                'top_up',    // Relleno
                'other',     // Otro
            ])->default('racking');
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('wine_id');
            $table->index('from_container_id');
            $table->index('to_container_id');
            $table->index('transfer_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_transfers');
    }
};
