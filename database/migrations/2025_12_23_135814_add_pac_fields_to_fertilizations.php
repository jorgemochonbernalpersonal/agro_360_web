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
        Schema::table('fertilizations', function (Blueprint $table) {
            // Unidades Fertilizantes (Normativa de Nutrición)
            $table->decimal('nitrogen_uf', 8, 3)->nullable()->comment('Unidades Fertilizantes N / ha');
            $table->decimal('phosphorus_uf', 8, 3)->nullable()->comment('Unidades Fertilizantes P / ha');
            $table->decimal('potassium_uf', 8, 3)->nullable()->comment('Unidades Fertilizantes K / ha');
            
            // Datos para fertilizantes orgánicos (Estiércoles)
            $table->string('manure_type', 100)->nullable()->comment('Tipo de estiércol');
            $table->date('burial_date')->nullable()->comment('Fecha de enterrado');
            $table->string('emission_reduction_method', 100)->nullable()->comment('Método de reducción de emisiones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fertilizations', function (Blueprint $table) {
            $table->dropColumn([
                'nitrogen_uf', 
                'phosphorus_uf', 
                'potassium_uf', 
                'manure_type', 
                'burial_date',
                'emission_reduction_method'
            ]);
        });
    }
};
