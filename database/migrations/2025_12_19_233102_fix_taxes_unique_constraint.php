<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            // Eliminar el índice único de code
            $table->dropUnique(['code']);
            
            // Agregar índice único compuesto (code, rate, region)
            $table->unique(['code', 'rate', 'region'], 'tax_code_rate_region_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            // Eliminar el índice único compuesto
            $table->dropUnique('tax_code_rate_region_unique');
            
            // Restaurar el índice único de code
            $table->unique('code');
        });
    }
};
