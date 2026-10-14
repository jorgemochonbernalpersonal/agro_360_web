<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Defensa en profundidad para el rastro de auditoría fiscal (ver auditoría de
 * facturación fix #15): además de bloquear el borrado de facturas no-borrador
 * a nivel de aplicación (InvoiceController::destroy /
 * GrapePurchaseInvoiceController::destroy), esta migración quita el
 * onDelete('cascade') de sif_records.invoice_id e invoice_audit_logs.invoice_id
 * y lo cambia por restrictOnDelete(): aunque alguien borre una factura
 * saltándose la capa de aplicación (tinker, script, bug futuro), la base de
 * datos rechaza el borrado mientras existan registros VERI*FACTU o de
 * auditoría asociados, en vez de arrastrarlos silenciosamente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sif_records', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
        });
        Schema::table('sif_records', function (Blueprint $table) {
            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->restrictOnDelete();
        });

        Schema::table('invoice_audit_logs', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
        });
        Schema::table('invoice_audit_logs', function (Blueprint $table) {
            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sif_records', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
        });
        Schema::table('sif_records', function (Blueprint $table) {
            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->cascadeOnDelete();
        });

        Schema::table('invoice_audit_logs', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
        });
        Schema::table('invoice_audit_logs', function (Blueprint $table) {
            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->cascadeOnDelete();
        });
    }
};
