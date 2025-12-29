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
        Schema::create('product_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('treatment_id')->nullable();
            $table->string('movement_type'); // 'purchase', 'consumption', 'adjustment', 'transfer', 'expired', 'damaged'
            $table->decimal('quantity_change', 10, 3);
            $table->decimal('quantity_before', 10, 3);
            $table->decimal('quantity_after', 10, 3);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('stock_id')->references('id')->on('product_stocks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('treatment_id')->references('id')->on('phytosanitary_treatments')->onDelete('set null');
            
            $table->index(['stock_id', 'movement_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_movements');
    }
};
