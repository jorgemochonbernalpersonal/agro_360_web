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
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->integer('activity_id');
            $table->string('observation_type', 50)->nullable(); // 'plaga', 'enfermedad', 'fenología', 'general'
            $table->text('description')->nullable();
            $table->json('photos')->nullable(); // Array de rutas de fotos
            $table->string('severity', 20)->nullable(); // 'leve', 'moderada', 'grave'
            $table->text('action_taken')->nullable();
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('agricultural_activities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
