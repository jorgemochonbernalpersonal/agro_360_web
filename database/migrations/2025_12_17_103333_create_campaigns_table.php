<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // Ej: "Campaña 2025"
            $table->integer('year');  // Año de la campaña
            $table->unsignedBigInteger('viticulturist_id');  // FK a users
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(false);  // Solo una activa por viticultor
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('viticulturist_id')->references('id')->on('users');

            $table->index('viticulturist_id');
            $table->index('year');
            $table->index('active');

            // Nota: La lógica de "solo una campaña activa" se maneja en el modelo Campaign
            // mediante el método activate() que desactiva las demás
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
