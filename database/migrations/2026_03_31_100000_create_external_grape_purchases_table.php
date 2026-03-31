<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_grape_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('destination_container_id')->nullable()->constrained('containers')->nullOnDelete();
            $table->foreignId('wine_id')->nullable()->constrained()->nullOnDelete();

            $table->string('supplier_name', 255)->nullable();
            $table->enum('product_type', ['grape', 'must', 'concentrated_must', 'rectified_must'])->default('grape');
            $table->string('variety', 100)->nullable();
            $table->string('origin', 255)->nullable();
            $table->unsignedSmallInteger('vintage_year')->nullable();
            $table->decimal('quantity_kg', 12, 3);
            $table->decimal('price_per_kg', 10, 4)->nullable();
            $table->decimal('total_price', 12, 2)->nullable();
            $table->date('purchase_date');
            $table->date('delivery_date')->nullable();

            // Trazabilidad
            $table->string('rega_code', 100)->nullable();
            $table->string('quality_certificate', 255)->nullable();

            // Parámetros de calidad
            $table->decimal('baume_degree', 5, 2)->nullable();
            $table->decimal('brix_degree', 5, 2)->nullable();
            $table->decimal('potential_alcohol', 5, 2)->nullable();
            $table->decimal('acidity_level', 5, 2)->nullable();
            $table->decimal('ph_level', 4, 2)->nullable();

            $table->enum('status', ['pending', 'received', 'processed', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'purchase_date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_grape_purchases');
    }
};
