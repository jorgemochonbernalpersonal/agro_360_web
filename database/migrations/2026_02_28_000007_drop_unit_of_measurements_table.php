<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('unit_of_measurements');
    }

    public function down(): void
    {
        Schema::create('unit_of_measurements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol', 20);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
};
