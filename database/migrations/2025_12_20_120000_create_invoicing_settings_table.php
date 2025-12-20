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
        Schema::create('invoicing_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Configuración de Facturas
            $table->string('invoice_prefix')->default('FAC-')->comment('Prefijo para facturas');
            $table->integer('invoice_padding')->default(4)->comment('Número de dígitos (ej: 4 = 0023)');
            $table->integer('invoice_counter')->default(1)->comment('Contador actual');
            $table->boolean('invoice_year_reset')->default(true)->comment('Resetear cada año');
            
            // Configuración de Albaranes
            $table->string('delivery_note_prefix')->default('ALB-')->comment('Prefijo para albaranes');
            $table->integer('delivery_note_padding')->default(4)->comment('Número de dígitos');
            $table->integer('delivery_note_counter')->default(1)->comment('Contador actual');
            $table->boolean('delivery_note_year_reset')->default(true)->comment('Resetear cada año');
            
            // Control de reseteo
            $table->integer('last_reset_year')->default(date('Y'))->comment('Último año de reseteo');
            
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoicing_settings');
    }
};
