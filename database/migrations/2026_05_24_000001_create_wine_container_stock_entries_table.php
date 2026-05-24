<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_container_stock_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_id')->constrained('wines')->cascadeOnDelete();
            $table->foreignId('container_id')->nullable()->constrained('containers')->nullOnDelete();
            $table->decimal('quantity_liters', 12, 3);
            $table->date('entry_date');
            $table->enum('source', ['initial_stock', 'adjustment', 'correction'])->default('initial_stock');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('wine_id');
            $table->index('container_id');
            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_container_stock_entries');
    }
};
