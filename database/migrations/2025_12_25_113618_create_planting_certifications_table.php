<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planting_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_planting_id')->constrained('plot_plantings')->onDelete('cascade');
            $table->enum('type', ['ecologico', 'do', 'doca', 'igp', 'vino_pago']);
            $table->string('certification_number');
            $table->string('certifying_body'); // Ej: CAECV, Consejo Regulador
            $table->date('certification_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'expired', 'suspended', 'pending'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['plot_planting_id', 'status']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_certifications');
    }
};
