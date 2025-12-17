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
        Schema::create('agricultural_activities', function (Blueprint $table) {
            $table->id();
            $table->integer('plot_id');
            $table->integer('viticulturist_id');
            $table->string('activity_type', 50); // 'phytosanitary', 'fertilization', 'irrigation', 'cultural', 'observation'
            $table->date('activity_date');
            $table->integer('crew_id')->nullable();
            $table->integer('machinery_id')->nullable(); // Para futuro
            $table->string('weather_conditions')->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('plot_id')->references('id')->on('plots');
            $table->foreign('viticulturist_id')->references('id')->on('users');
            $table->foreign('crew_id')->references('id')->on('crews')->nullOnDelete();
            
            $table->index('plot_id');
            $table->index('viticulturist_id');
            $table->index('activity_type');
            $table->index('activity_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agricultural_activities');
    }
};
