<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_bottlings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wine_process_detail_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_lot_id')->nullable()->constrained('wine_lots')->nullOnDelete();
            $table->foreignId('oenologist_id')->nullable()->constrained()->nullOnDelete();
            $table->date('bottling_date');
            $table->string('bottle_format', 20);   // '187', '375', '750', '1500', 'otro', etc.
            $table->unsignedInteger('quantity_bottles');
            $table->decimal('quantity_liters', 10, 3);
            $table->string('lot_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_bottlings');
    }
};
