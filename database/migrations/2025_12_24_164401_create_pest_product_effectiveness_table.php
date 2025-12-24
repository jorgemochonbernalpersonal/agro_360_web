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
        Schema::create('pest_product_effectiveness', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pest_id')->constrained('pests')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('phytosanitary_products')->onDelete('cascade');
            $table->tinyInteger('effectiveness_rating')->default(3)->comment('Eficacia 1-5 estrellas');
            $table->text('notes')->nullable()->comment('Notas adicionales');
            $table->timestamps();
            
            $table->unique(['pest_id', 'product_id']);
            $table->index('effectiveness_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pest_product_effectiveness');
    }
};
