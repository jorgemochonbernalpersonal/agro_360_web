<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phytosanitary_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();

            $table->string('title', 255);
            $table->string('source', 100);          // mapa, consejeria, denominacion, otro
            $table->string('alert_type', 30);        // plaga, enfermedad, climatologica, normativa, otro
            $table->string('severity', 15);          // baja, media, alta, critica
            $table->string('affected_area', 255)->nullable();
            $table->text('description');
            $table->text('recommendations')->nullable();
            $table->date('alert_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['viticulturist_id', 'active']);
            $table->index(['viticulturist_id', 'alert_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phytosanitary_alerts');
    }
};
