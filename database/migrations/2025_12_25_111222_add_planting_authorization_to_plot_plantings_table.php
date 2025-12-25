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
        Schema::table('plot_plantings', function (Blueprint $table) {
            // Autorización de plantación (obligatorio para plantaciones post-2016)
            $table->string('planting_authorization')->nullable()->after('notes')
                ->comment('Número de autorización de plantación vitícola');
            
            $table->date('authorization_date')->nullable()->after('planting_authorization')
                ->comment('Fecha de concesión de la autorización');
            
            $table->enum('right_type', ['nueva', 'replantacion', 'conversion'])->nullable()->after('authorization_date')
                ->comment('Tipo de derecho: nueva plantación, replantación o conversión');
            
            $table->date('uprooting_date')->nullable()->after('right_type')
                ->comment('Fecha de arranque (solo para replantaciones)');
            
            // Denominación de Origen
            $table->string('designation_of_origin')->nullable()->after('uprooting_date')
                ->comment('DO, DOCa o IGP (ej: DO Rioja, DOCa Priorat)');
            
            // Índices
            $table->index('planting_authorization');
            $table->index('authorization_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plot_plantings', function (Blueprint $table) {
            $table->dropIndex(['planting_authorization']);
            $table->dropIndex(['authorization_date']);
            $table->dropColumn([
                'planting_authorization',
                'authorization_date',
                'right_type',
                'uprooting_date',
                'designation_of_origin',
            ]);
        });
    }
};
