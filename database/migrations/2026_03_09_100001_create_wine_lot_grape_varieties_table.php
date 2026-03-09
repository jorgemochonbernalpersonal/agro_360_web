<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_lot_grape_varieties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_lot_id')->constrained('wine_lots')->cascadeOnDelete();
            $table->foreignId('grape_variety_id')->constrained('grape_varieties')->cascadeOnDelete();
            $table->decimal('percentage', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['wine_lot_id', 'grape_variety_id']);
            $table->index('wine_lot_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_lot_grape_varieties');
    }
};
