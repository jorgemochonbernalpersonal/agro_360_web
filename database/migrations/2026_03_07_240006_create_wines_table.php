<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->smallInteger('vintage')->nullable()->comment('Añada');
            $table->enum('wine_type', [
                'red', 'white', 'rose', 'sparkling', 'fortified',
                'sweet', 'semi_sweet', 'other',
            ])->default('red');
            $table->enum('status', [
                'in_progress', 'aged', 'bottled', 'sold', 'cancelled',
            ])->default('in_progress');
            $table->string('variety')->nullable()->comment('Variedad o descripción del coupage');
            $table->decimal('volume_liters', 12, 3)->nullable()->comment('Volumen total en litros');
            $table->string('internal_code')->nullable()->comment('Código interno de bodega');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'vintage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wines');
    }
};
