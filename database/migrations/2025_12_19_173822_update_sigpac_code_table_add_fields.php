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
        Schema::table('sigpac_code', function (Blueprint $table) {
            // Agregar campos de estructura SIGPAC completa
            $table->string('code_polygon', 10)->nullable()->after('id');
            $table->string('code_plot', 10)->nullable()->after('code_polygon');
            $table->string('code_enclosure', 10)->nullable()->after('code_plot');
            $table->string('code_aggregate', 10)->nullable()->after('code_enclosure');
            $table->string('code_province', 10)->nullable()->after('code_aggregate');
            $table->string('code_zone', 10)->nullable()->after('code_province');
            $table->string('code_municipality', 10)->nullable()->after('code_zone');
            
            // El campo 'code' ya existe, lo hacemos nullable y cambiamos tamaño
            $table->string('code', 30)->nullable()->change();
            
            // Índices adicionales
            $table->index('code_municipality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sigpac_code', function (Blueprint $table) {
            $table->dropColumn([
                'code_polygon',
                'code_plot',
                'code_enclosure',
                'code_aggregate',
                'code_province',
                'code_zone',
                'code_municipality',
            ]);
            
            $table->dropIndex(['code_municipality']);
            $table->string('code')->change(); // Revertir a non-nullable
        });
    }
};
