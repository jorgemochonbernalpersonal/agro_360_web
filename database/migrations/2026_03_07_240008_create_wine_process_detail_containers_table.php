<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_process_detail_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_process_detail_id')->constrained('wine_process_details')->cascadeOnDelete();
            $table->foreignId('container_id')->constrained('containers')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->nullable()->comment('Cantidad en este contenedor');
            $table->foreignId('unit_of_measurement_id')->nullable()->constrained('units_of_measurement')->nullOnDelete();
            $table->timestamps();

            $table->unique(['wine_process_detail_id', 'container_id'], 'uq_process_container');
            $table->index('container_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_process_detail_containers');
    }
};
