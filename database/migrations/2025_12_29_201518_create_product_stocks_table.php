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
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->string('batch_number')->nullable(); // Número de lote
            $table->date('expiry_date')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->decimal('quantity', 10, 3)->default(0);
            $table->string('unit', 20)->default('L'); // L, kg, unidades
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->string('invoice_number')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('phytosanitary_products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            
            $table->index(['product_id', 'user_id', 'active']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
