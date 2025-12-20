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
        Schema::create('cultural_works', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->string('work_type', 100)->nullable(); // 'poda', 'vendimia', 'deshojado', 'acolchado', etc.
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->integer('workers_count')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('agricultural_activities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cultural_works');
    }
};
