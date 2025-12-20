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
        Schema::create('fertilizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->string('fertilizer_type', 100)->nullable(); // 'orgánico', 'mineral', 'compost', etc.
            $table->string('fertilizer_name')->nullable();
            $table->decimal('quantity', 10, 3)->nullable(); // kg o litros
            $table->string('npk_ratio', 50)->nullable(); // "10-20-10"
            $table->string('application_method', 50)->nullable();
            $table->decimal('area_applied', 10, 3)->nullable();
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('agricultural_activities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fertilizations');
    }
};
