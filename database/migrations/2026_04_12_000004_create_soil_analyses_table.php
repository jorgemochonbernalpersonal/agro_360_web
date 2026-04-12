<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soil_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();

            $table->date('analysis_date');
            $table->string('laboratory', 255)->nullable();
            $table->unsignedSmallInteger('sample_depth_cm')->nullable();

            $table->decimal('ph', 4, 2)->nullable();
            $table->decimal('organic_matter', 5, 2)->nullable();          // %
            $table->decimal('nitrogen_total', 6, 2)->nullable();          // mg/kg
            $table->decimal('phosphorus', 6, 2)->nullable();              // mg/kg Olsen
            $table->decimal('potassium', 6, 2)->nullable();               // mg/kg
            $table->decimal('calcium', 6, 2)->nullable();                 // mg/kg
            $table->decimal('magnesium', 6, 2)->nullable();               // mg/kg
            $table->string('texture_class', 30)->nullable();              // arenoso, franco-arenoso, franco, franco-arcilloso, arcilloso
            $table->decimal('electrical_conductivity', 6, 2)->nullable(); // dS/m
            $table->decimal('limestone', 5, 2)->nullable();               // % caliza activa

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['viticulturist_id', 'plot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soil_analyses');
    }
};
