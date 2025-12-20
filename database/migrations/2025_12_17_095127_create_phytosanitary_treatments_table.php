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
        Schema::create('phytosanitary_treatments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('dose_per_hectare', 10, 3)->nullable();
            $table->decimal('total_dose', 10, 3)->nullable();
            $table->decimal('area_treated', 10, 3)->nullable();
            $table->string('application_method', 50)->nullable(); // 'pulverización', 'aplicación foliar', etc.
            $table->string('target_pest')->nullable(); // Plaga o enfermedad objetivo
            $table->decimal('wind_speed', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('agricultural_activities')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('phytosanitary_products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phytosanitary_treatments');
    }
};
