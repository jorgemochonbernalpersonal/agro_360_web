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
        try {
            Schema::table('invoices', function (Blueprint $table) {
                // Intentar quitar el índice único
                $table->dropUnique(['invoice_number']);
            });
        } catch (\Exception $e) {
            // El índice no existe, continuar
        }
        
        Schema::table('invoices', function (Blueprint $table) {
            // Hacer la columna nullable
            $table->string('invoice_number')->nullable()->change();
        });
        
        Schema::table('invoices', function (Blueprint $table) {
            // Volver a crear el índice único (permitirá múltiples NULLs)
            $table->unique('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Quitar el índice único
            $table->dropUnique(['invoice_number']);
        });
        
        Schema::table('invoices', function (Blueprint $table) {
            // Volver a hacer NOT NULL
            $table->string('invoice_number')->nullable(false)->change();
        });
        
        Schema::table('invoices', function (Blueprint $table) {
            // Volver a crear el índice único
            $table->unique('invoice_number');
        });
    }
};
