<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_harvests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_id')->constrained('wines')->cascadeOnDelete();
            $table->foreignId('harvest_id')->constrained('harvests')->cascadeOnDelete();
            $table->decimal('quantity_kg', 12, 3)->comment('Kg aportados a este lote');
            $table->decimal('percentage', 5, 2)->nullable()->comment('% sobre el total del lote');
            $table->timestamps();

            $table->unique(['wine_id', 'harvest_id']);
            $table->index('wine_id');
            $table->index('harvest_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_harvests');
    }
};
