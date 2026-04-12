<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planned_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plot_id')->nullable()->constrained()->nullOnDelete();

            $table->string('category', 30);       // tratamiento, fertilizacion, riego, labor_cultural, poda, vendimia, observacion, post_vendimia, otro
            $table->string('title', 255);
            $table->text('description')->nullable();

            $table->date('planned_date');
            $table->date('planned_end_date')->nullable();
            $table->string('priority', 15)->default('media');   // baja, media, alta, urgente
            $table->string('status', 15)->default('pendiente'); // pendiente, en_progreso, completada, cancelada

            // Morph al registro del cuaderno cuando se ejecuta
            $table->nullableMorphs('linked_entry');

            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['viticulturist_id', 'status']);
            $table->index(['viticulturist_id', 'planned_date']);
            $table->index(['viticulturist_id', 'campaign_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planned_works');
    }
};
