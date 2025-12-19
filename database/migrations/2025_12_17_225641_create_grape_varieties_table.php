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
        Schema::create('grape_varieties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nombre de la variedad de uva');
            $table->string('code', 10)->nullable()->unique()->comment('Código de la variedad');
            $table->enum('color', ['red', 'white', 'rose'])->nullable()->comment('Color de la uva');
            $table->text('description')->nullable()->comment('Descripción y características');
            $table->boolean('active')->default(true)->comment('Si está activa');
            $table->timestamps();
            
            $table->index('active');
            $table->index('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grape_varieties');
    }
};
