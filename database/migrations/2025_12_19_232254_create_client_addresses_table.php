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
        Schema::create('client_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            
            // Información de contacto en la dirección
            $table->string('name')->nullable()->comment('Nombre de la dirección (ej: Oficina, Almacén)');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position')->nullable()->comment('Cargo/Posición');
            
            // Dirección física
            $table->text('address')->nullable();
            
            // Para España (usando tablas existentes)
            $table->foreignId('autonomous_community_id')->nullable()->constrained('autonomous_communities')->onDelete('set null');
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onDelete('set null');
            $table->foreignId('municipality_id')->nullable()->constrained('municipalities')->onDelete('set null');
            $table->string('postal_code')->nullable();
            
            // Configuración
            $table->boolean('is_default')->default(false);
            $table->boolean('is_delivery_note_address')->default(false)->comment('Usar para albaranes');
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            $table->index('client_id');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_addresses');
    }
};
