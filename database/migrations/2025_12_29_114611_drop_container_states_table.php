<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Eliminar tabla container_states obsoleta
     * Los datos ya fueron migrados a container_current_states
     */
    public function up(): void
    {
        if (Schema::hasTable('container_states')) {
            Schema::dropIfExists('container_states');
        }
    }

    /**
     * Reverse the migrations.
     * Nota: No recreamos la tabla porque los datos ya están en container_current_states
     */
    public function down(): void
    {
        // No revertir - los datos ya están consolidados en container_current_states
    }
};
