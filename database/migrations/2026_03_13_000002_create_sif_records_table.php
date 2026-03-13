<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sif_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->enum('tipo_registro', ['ALTA', 'ANULACION'])->default('ALTA');
            $table->string('csv', 100)->nullable()->comment('Código Seguro de Verificación AEAT');
            $table->string('registro_aeat', 100)->nullable();
            $table->string('hash_registro', 64)->nullable();
            $table->mediumText('request_xml')->nullable();
            $table->mediumText('response_xml')->nullable();
            $table->enum('status', ['WD', 'OK', 'ER'])->default('WD')->comment('WD=Waiting, OK=Success, ER=Error');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sif_records');
    }
};
