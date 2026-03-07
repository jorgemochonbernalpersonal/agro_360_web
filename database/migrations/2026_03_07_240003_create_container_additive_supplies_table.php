<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_additive_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained('containers')->cascadeOnDelete();
            $table->foreignId('container_current_state_id')->nullable()->constrained('container_current_states')->nullOnDelete();
            $table->foreignId('winery_supply_id')->nullable()->constrained('winery_supplies')->nullOnDelete();
            $table->string('additive_name')->nullable()->comment('Nombre libre si no está en el catálogo');
            $table->decimal('quantity', 12, 3);
            $table->foreignId('unit_of_measurement_id')->nullable()->constrained('units_of_measurement')->nullOnDelete();
            $table->date('additive_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['container_id', 'additive_date']);
            $table->index('container_current_state_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_additive_supplies');
    }
};
