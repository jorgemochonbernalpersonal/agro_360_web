<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_lot_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_lot_id')->constrained('wine_lots')->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained('taxes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['wine_lot_id', 'tax_id']);
            $table->index('wine_lot_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_lot_taxes');
    }
};
