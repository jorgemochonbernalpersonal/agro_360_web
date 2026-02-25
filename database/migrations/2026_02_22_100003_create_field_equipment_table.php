<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');

            $table->string('name');
            $table->enum('equipment_type', [
                'sprayer',      // Pulverizador
                'spreader',     // Abonadora/Esparcidora
                'irrigation',   // Equipo de riego
                'tractor',      // Tractor
                'harvester',    // Vendimidora
                'pruner',       // Podadora
                'mower',        // Segadora/Trituradora
                'other',        // Otro
            ])->default('sprayer');
            $table->string('registration_number', 50)->nullable()
                ->comment('Matrícula, serie o número de registro del equipo');
            $table->date('purchase_date')->nullable();
            $table->date('last_inspection_date')->nullable()
                ->comment('Última inspección técnica obligatoria (ITB para pulverizadores)');
            $table->date('next_inspection_date')->nullable()
                ->comment('Próxima inspección programada');
            $table->string('inspection_entity', 100)->nullable()
                ->comment('Entidad que realizó/realizará la inspección');
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['viticulturist_id', 'active']);
            $table->index(['viticulturist_id', 'equipment_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_equipment');
    }
};
