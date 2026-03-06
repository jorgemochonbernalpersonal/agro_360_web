<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Invoices: tipo de factura + viticultor como destinatario (compra de uva)
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_type', 20)->nullable()->after('user_id')
                ->comment('wine_sale | grape_purchase');
            $table->foreignId('viticulturist_id')->nullable()->after('client_id')
                ->constrained('users')->onDelete('set null')
                ->comment('Viticultor destinatario (factura de vendimia)');
        });

        // Hacer client_id nullable para facturas de vendimia (sin cliente externo)
        DB::statement('ALTER TABLE invoices MODIFY client_id BIGINT UNSIGNED NULL');

        // Invoice items: FK al lote de vino + añadir concept_type 'wine'
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('wine_lot_id')->nullable()->after('harvest_id')
                ->constrained('wine_lots')->onDelete('set null');
        });

        DB::statement(
            "ALTER TABLE invoice_items MODIFY concept_type
             ENUM('harvest','service','product','other','wine') DEFAULT 'harvest'"
        );
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['wine_lot_id']);
            $table->dropColumn('wine_lot_id');
        });

        DB::statement(
            "ALTER TABLE invoice_items MODIFY concept_type
             ENUM('harvest','service','product','other') DEFAULT 'harvest'"
        );

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['viticulturist_id']);
            $table->dropColumn(['invoice_type', 'viticulturist_id']);
        });

        DB::statement('ALTER TABLE invoices MODIFY client_id BIGINT UNSIGNED NOT NULL');
    }
};
