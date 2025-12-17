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
        Schema::create('phytosanitary_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('active_ingredient')->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('type', 50)->nullable(); // 'herbicida', 'fungicida', 'insecticida', etc.
            $table->string('toxicity_class', 20)->nullable(); // 'I', 'II', 'III', 'IV'
            $table->integer('withdrawal_period_days')->nullable(); // Plazo de seguridad en días
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phytosanitary_products');
    }
};
