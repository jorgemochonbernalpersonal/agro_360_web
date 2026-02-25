<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residue_managements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('plot_id')->nullable()->constrained('plots')->onDelete('set null');
            $table->foreignId('plot_planting_id')->nullable()->constrained('plot_plantings')->onDelete('set null');
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');

            $table->date('date');

            $table->enum('practice_type', [
                'incorporation',  // Triturado e incorporación al suelo
                'removal',        // Retirada de la explotación
                'burning',        // Quema (cuando permitido)
                'composting',     // Compostaje
                'biogas',         // Biogás
                'sale',           // Venta
                'other',
            ])->comment('Práctica de gestión aplicada');

            $table->enum('material_type', [
                'pruning_wood',   // Leña/madera de poda
                'grape_marc',     // Orujo
                'vine_leaves',    // Hojas de vid
                'grass',          // Cubierta vegetal
                'other',
            ])->comment('Tipo de residuo/material gestionado');

            $table->decimal('estimated_quantity', 10, 2)->nullable()->comment('Cantidad estimada (kg o t)');
            $table->string('quantity_unit', 10)->nullable()->comment('Unidad: kg, t');
            $table->text('justification')->nullable()->comment('Justificación (obligatoria para quema)');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['campaign_id', 'active']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residue_managements');
    }
};
