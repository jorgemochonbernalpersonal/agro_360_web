<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si la tabla ya existe (por si hubo una ejecución parcial anterior)
        if (Schema::hasTable('container_current_states')) {
            return;
        }

        Schema::create('container_current_states', function (Blueprint $table) {
            $table->id();

            // Contenedor único (un estado por contenedor)
            $table
                ->foreignId('container_id')
                ->unique()
                ->constrained('containers')
                ->onDelete('cascade');

            // Referencias al contenido actual
            $table->unsignedBigInteger('wine_id')->nullable()->comment('ID de vino (tabla futura)');
            $table->unsignedBigInteger('wine_process_detail_id')->nullable()->comment('Proceso de vinificación');
            $table->foreignId('harvest_id')->nullable()->constrained('harvests')->onDelete('set null');
            $table->unsignedBigInteger('external_grape_id')->nullable()->comment('Uva comprada externa');

            // Control de subproductos
            $table->boolean('has_subproducts')->default(false);

            // Cantidad actual en el contenedor
            $table->decimal('current_quantity', 10, 2)->default(0.0)->comment('Cantidad actual en kg');

            $table->timestamps();

            // Índices
            $table->index('container_id');
            $table->index('harvest_id');
            $table->index('wine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_current_states');
    }
};
