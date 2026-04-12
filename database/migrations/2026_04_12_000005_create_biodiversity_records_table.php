<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biodiversity_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();

            $table->string('record_type', 30);        // cubierta_vegetal, margen, seto, fauna_auxiliar, nido, hotel_insectos, otro
            $table->text('description')->nullable();
            $table->decimal('area_m2', 10, 2)->nullable();
            $table->string('species', 500)->nullable();
            $table->date('record_date');
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['viticulturist_id', 'plot_id']);
            $table->index(['viticulturist_id', 'record_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biodiversity_records');
    }
};
