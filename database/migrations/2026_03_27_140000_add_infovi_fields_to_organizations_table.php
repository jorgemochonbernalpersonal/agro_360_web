<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('reovi_number', 50)->nullable()->after('vat_number')
                  ->comment('Número de registro en el REOVI (Registro de Operadores Vitivinícolas)');
            $table->string('nidpb', 50)->nullable()->after('reovi_number')
                  ->comment('Número de Identificación del Depósito o Punto de Bodega (instalación INFOVI)');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['reovi_number', 'nidpb']);
        });
    }
};
