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
        Schema::table('harvests', function (Blueprint $table) {
            $table->string('transport_document_number', 50)->nullable()->comment('Nº Documento de Acompañamiento');
            $table->string('destination_rega_code', 20)->nullable()->comment('Código REGA de destino');
            $table->string('vehicle_plate', 20)->nullable()->comment('Matrícula del vehículo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropColumn(['transport_document_number', 'destination_rega_code', 'vehicle_plate']);
        });
    }
};
