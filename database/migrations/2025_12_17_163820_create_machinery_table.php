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
        Schema::create('machinery', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // tractor, pulverizador, vendimiadora, etc.
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->integer('year')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            $table->string('roma_registration')->nullable(); // Inscripción ROMA
            $table->boolean('is_rented')->default(false); // Si es alquilada
            $table->string('capacity')->nullable(); // Capacidad
            $table->date('last_revision_date')->nullable(); // Fecha de última revisión
            $table->string('image')->nullable(); // Imagen
            $table->text('notes')->nullable(); // Notas (textarea)
            $table->unsignedBigInteger('viticulturist_id');
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->foreign('viticulturist_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['viticulturist_id', 'active']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machinery');
    }
};
