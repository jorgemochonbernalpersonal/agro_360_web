<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabla para trackear el progreso del onboarding de nuevos viticultores
     */
    public function up(): void
    {
        Schema::create('onboarding_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('step', 50); // 'create_plot', 'register_activity', etc.
            $table->timestamp('completed_at')->nullable();
            $table->boolean('skipped')->default(false);
            $table->timestamps();
            
            // Un usuario solo puede tener un registro por step
            $table->unique(['user_id', 'step']);
            
            // Índice para queries frecuentes
            $table->index(['user_id', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_progress');
    }
};
