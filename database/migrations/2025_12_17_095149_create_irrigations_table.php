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
        Schema::create('irrigations', function (Blueprint $table) {
            $table->id();
            $table->integer('activity_id');
            $table->decimal('water_volume', 10, 3)->nullable(); // Litros o m³
            $table->string('irrigation_method', 50)->nullable(); // 'goteo', 'aspersión', 'inundación', etc.
            $table->integer('duration_minutes')->nullable();
            $table->decimal('soil_moisture_before', 5, 2)->nullable();
            $table->decimal('soil_moisture_after', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('agricultural_activities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('irrigations');
    }
};
