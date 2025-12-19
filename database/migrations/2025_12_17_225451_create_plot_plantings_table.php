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
        Schema::create('plot_plantings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained('plots')->onDelete('cascade');
            // Usamos una foreignId simple para evitar problemas de orden de migraciones en tests.
            // La integridad referencial se controla a nivel de aplicación.
            $table->foreignId('grape_variety_id')->nullable();
            
            // Superficie y plantación
            $table->decimal('area_planted', 10, 3)->comment('Superficie dedicada a esta variedad (hectáreas)');
            $table->integer('planting_year')->nullable()->comment('Año de plantación');
            $table->date('planting_date')->nullable()->comment('Fecha exacta de plantación');
            
            // Densidad y distribución
            $table->integer('vine_count')->nullable()->comment('Número de cepas');
            $table->integer('density')->nullable()->comment('Cepas por hectárea');
            $table->decimal('row_spacing', 10, 3)->nullable()->comment('Distancia entre filas (metros)');
            $table->decimal('vine_spacing', 10, 3)->nullable()->comment('Distancia entre cepas (metros)');
            
            // Características técnicas
            $table->string('rootstock')->nullable()->comment('Portainjerto utilizado');
            $table->string('training_system')->nullable()->comment('Sistema de conducción (espaldera, vaso, etc.)');
            $table->boolean('irrigated')->default(false)->comment('Si tiene riego');
            
            // Estado y notas
            $table->enum('status', ['active', 'removed', 'experimental', 'replanting'])->default('active')->comment('Estado de la plantación');
            $table->text('notes')->nullable()->comment('Observaciones adicionales');
            
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index(['plot_id', 'grape_variety_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_plantings');
    }
};
