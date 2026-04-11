<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Three-bucket columns on wine_lots: available / reserved / sold
        if (! Schema::hasColumn('wine_lots', 'reserved_quantity')) {
            Schema::table('wine_lots', function (Blueprint $table) {
                $table->decimal('reserved_quantity', 10, 3)->default(0)->after('available_quantity')
                    ->comment('Comprometido en facturas activas, pendiente de entrega');
                $table->decimal('sold_quantity', 10, 3)->default(0)->after('reserved_quantity')
                    ->comment('Vendido y entregado definitivamente');
            });
        }

        // 2. Atomic sequential counters per winery user (locked per transaction)
        if (! Schema::hasColumn('users', 'wine_sale_seq')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('wine_sale_seq')->default(0)->after('id')
                    ->comment('Contador para numeración de facturas de venta de vino');
                $table->unsignedInteger('grape_purchase_seq')->default(0)->after('wine_sale_seq')
                    ->comment('Contador para numeración de liquidaciones de vendimia');
            });
        }

        // 3. Stock movement audit log (equivalent to vinai2 HistoryStocks)
        if (Schema::hasTable('invoice_stock_movements')) {
            return;
        }
        Schema::create('invoice_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->onDelete('set null');
            $table->foreignId('wine_lot_id')->nullable()->constrained('wine_lots')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('qty', 10, 3)->comment('Cantidad movida');
            $table->enum('action', ['create', 'deliver', 'cancel'])->comment('Tipo de movimiento');
            $table->enum('from_bucket', ['available', 'reserved', 'sold'])->nullable();
            $table->enum('to_bucket', ['available', 'reserved', 'sold'])->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('wine_lot_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_stock_movements');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['wine_sale_seq', 'grape_purchase_seq']);
        });

        Schema::table('wine_lots', function (Blueprint $table) {
            $table->dropColumn(['reserved_quantity', 'sold_quantity']);
        });
    }
};
