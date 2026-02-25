<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('activity_id')->nullable()
                ->constrained('agricultural_activities')->onDelete('set null');
            $table->foreignId('machinery_id')->nullable()
                ->constrained('machinery')->onDelete('set null');

            $table->date('date');
            $table->enum('energy_type', [
                'diesel',
                'gasoline',
                'electricity',
                'lpg',
                'natural_gas',
                'water_pump',
                'other',
            ])->comment('Tipo de energía consumida');
            $table->enum('unit', ['liters', 'kwh', 'm3', 'kg'])->default('liters');
            $table->decimal('quantity', 10, 3)->comment('Cantidad consumida');
            $table->decimal('cost_per_unit', 10, 4)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->decimal('co2_kg_equivalent', 10, 3)->nullable()->comment('kg CO₂ equivalente calculado');
            $table->string('usage_description')->nullable()->comment('Descripción de la operación');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['campaign_id', 'date']);
            $table->index(['viticulturist_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_usages');
    }
};
