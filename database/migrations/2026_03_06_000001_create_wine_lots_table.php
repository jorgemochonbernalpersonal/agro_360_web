<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->unsignedSmallInteger('vintage')->nullable()->comment('Año de vendimia');
            $table->enum('wine_type', ['tinto', 'blanco', 'rosado', 'espumoso', 'otro'])->default('tinto');
            $table->decimal('quantity', 10, 3)->default(0)->comment('Cantidad total producida');
            $table->enum('unit', ['litros', 'botellas', 'cajas'])->default('litros');
            $table->decimal('available_quantity', 10, 3)->default(0)->comment('Cantidad disponible para venta');
            $table->decimal('price_per_unit', 10, 4)->default(0)->comment('Precio unitario por defecto');
            $table->text('notes')->nullable();
            $table->boolean('archived')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('archived');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_lots');
    }
};
