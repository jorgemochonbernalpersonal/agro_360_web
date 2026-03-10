<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oenologists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('surname', 150)->nullable();
            $table->string('license_number', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oenologists');
    }
};
