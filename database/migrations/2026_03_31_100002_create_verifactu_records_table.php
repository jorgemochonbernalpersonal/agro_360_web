<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifactu_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->enum('submission_status', ['pending', 'queued', 'submitted', 'accepted', 'rejected', 'cancelled'])
                  ->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            // AEAT Código Seguro de Verificación (CSV)
            $table->string('aeat_csv', 100)->nullable();
            // Encadenamiento (hash del registro anterior en la cadena)
            $table->string('chain_hash', 128)->nullable();
            // Datos para el QR de verificación
            $table->text('qr_data')->nullable();
            $table->string('response_code', 20)->nullable();
            $table->text('response_message')->nullable();
            $table->json('error_details')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'submission_status']);
            $table->index(['invoice_id']);
            $table->unique('invoice_id'); // one VeriFactu record per invoice
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifactu_records');
    }
};
