<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('capacity')->nullable()->comment('Número máximo de contenedores');
            $table->decimal('temperature', 5, 2)->nullable()->comment('Temperatura en °C');
            $table->decimal('humidity', 5, 2)->nullable()->comment('Humedad relativa %');
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_rooms');
    }
};
