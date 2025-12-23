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
        // Índices para harvest_stocks (performance en queries latest())
        Schema::table('harvest_stocks', function (Blueprint $table) {
            $table->index(['harvest_id', 'created_at'], 'harvest_stocks_harvest_created_idx');
            $table->index('invoice_item_id', 'harvest_stocks_invoice_item_idx');
            $table->index('container_id', 'harvest_stocks_container_idx');
        });

        // Índices para invoice_items (validación duplicados y soft deletes)
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->index('harvest_id', 'invoice_items_harvest_idx');
            $table->index('deleted_at', 'invoice_items_deleted_idx');
            $table->index('invoice_id', 'invoice_items_invoice_idx');
        });

        // Índices para container_states (queries de stock)
        Schema::table('container_states', function (Blueprint $table) {
            $table->index('harvest_id', 'container_states_harvest_idx');
            $table->index(['container_id', 'last_movement_at'], 'container_states_container_movement_idx');
        });

        // Índices para invoices (queries frecuentes)
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'invoices_user_status_created_idx');
            $table->index('delivery_note_code', 'invoices_delivery_note_idx');
            $table->index('invoice_number', 'invoices_invoice_number_idx');
            $table->index(['status', 'created_at'], 'invoices_status_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harvest_stocks', function (Blueprint $table) {
            $table->dropIndex('harvest_stocks_harvest_created_idx');
            $table->dropIndex('harvest_stocks_invoice_item_idx');
            $table->dropIndex('harvest_stocks_container_idx');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex('invoice_items_harvest_idx');
            $table->dropIndex('invoice_items_deleted_idx');
            $table->dropIndex('invoice_items_invoice_idx');
        });

        Schema::table('container_states', function (Blueprint $table) {
            $table->dropIndex('container_states_harvest_idx');
            $table->dropIndex('container_states_container_movement_idx');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_user_status_created_idx');
            $table->dropIndex('invoices_delivery_note_idx');
            $table->dropIndex('invoices_invoice_number_idx');
            $table->dropIndex('invoices_status_created_idx');
        });
    }
};
