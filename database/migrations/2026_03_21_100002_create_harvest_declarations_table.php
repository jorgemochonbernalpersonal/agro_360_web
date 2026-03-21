<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvest_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');

            $table->unsignedSmallInteger('declaration_year')->comment('Año de campaña declarado');
            $table->date('declaration_date')->comment('Fecha de elaboración de la declaración');
            $table->date('submission_date')->nullable()->comment('Fecha de presentación ante el organismo');
            $table->string('authority')->comment('Organismo receptor (CCAA, DO, MAPA, etc.)');
            $table->string('reference_number')->nullable()->comment('Número de referencia oficial asignado');
            $table->decimal('total_surface_ha', 10, 4)->nullable()->comment('Superficie total declarada (ha)');
            $table->decimal('total_kg', 12, 2)->nullable()->comment('Producción total declarada (kg)');
            $table->json('declaration_lines')->nullable()
                ->comment('Detalle por variedad: [{variety, plot_name, surface_ha, kg, destination, rega_code, buyer}]');
            $table->enum('status', ['draft', 'submitted', 'accepted', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['viticulturist_id', 'declaration_year']);
            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvest_declarations');
    }
};
