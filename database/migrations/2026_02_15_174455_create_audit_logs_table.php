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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Usuario que realiza la acción
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Modelo auditado
            $table->string('auditable_type'); // Nombre del modelo (Plot, Activity, etc.)
            $table->unsignedBigInteger('auditable_id'); // ID del modelo
            
            // Acción realizada
            $table->string('event'); // created, updated, deleted, restored
            
            // IP y User Agent
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // URL de la petición
            $table->string('url')->nullable();
            
            // Datos anteriores (para updates y deletes)
            $table->json('old_values')->nullable();
            
            // Datos nuevos (para creates y updates)
            $table->json('new_values')->nullable();
            
            // Metadatos adicionales
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            
            // Índices para búsquedas eficientes
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('event');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
