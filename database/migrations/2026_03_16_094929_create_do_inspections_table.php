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
        Schema::create('do_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();

            $table->enum('subject_type', ['winery', 'viticulturist']);
            $table->foreignId('subject_id')->constrained('users')->cascadeOnDelete();

            $table->date('inspection_date');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->enum('result', ['compliant', 'non_compliant', 'pending'])->nullable();
            $table->text('findings')->nullable();
            $table->text('notes')->nullable();
            $table->string('reference_number')->nullable();
            $table->timestamps();

            $table->index(['supervisor_id', 'status']);
            $table->index(['supervisor_id', 'inspection_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('do_inspections');
    }
};
