<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina la tabla `verifactu_records`, un sistema paralelo que nunca llegó a
 * declarar ante la AEAT. VeriFactu opera ahora exclusivamente sobre
 * invoices.sif_* + sif_records (web y API móvil comparten estado).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('verifactu_records');
    }

    public function down(): void
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
            $table->string('aeat_csv', 100)->nullable();
            $table->string('chain_hash', 128)->nullable();
            $table->text('qr_data')->nullable();
            $table->string('response_code', 20)->nullable();
            $table->text('response_message')->nullable();
            $table->json('error_details')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'submission_status']);
            $table->index(['invoice_id']);
            $table->unique('invoice_id');
        });
    }
};
