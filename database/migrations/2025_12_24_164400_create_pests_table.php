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
        Schema::create('pests', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['pest', 'disease'])->comment('Tipo: plaga o enfermedad');
            $table->string('name', 100)->comment('Nombre común');
            $table->string('scientific_name', 150)->nullable()->comment('Nombre científico');
            $table->text('description')->nullable()->comment('Descripción detallada');
            $table->text('symptoms')->nullable()->comment('Síntomas y signos');
            $table->text('lifecycle')->nullable()->comment('Ciclo de vida');
            $table->json('risk_months')->nullable()->comment('Meses de mayor riesgo (1-12)');
            $table->string('threshold', 255)->nullable()->comment('Umbral de tratamiento');
            $table->text('prevention_methods')->nullable()->comment('Métodos de prevención');
            $table->json('photos')->nullable()->comment('Rutas de fotos');
            $table->boolean('active')->default(true)->comment('Activo/Inactivo');
            $table->timestamps();
            
            $table->index('type');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pests');
    }
};
