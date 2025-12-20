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
        Schema::create('plot_sigpac_use', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plot_id');
            $table->unsignedBigInteger('sigpac_use_id');
            $table->timestamps();

            $table->foreign('plot_id')->references('id')->on('plots')->onDelete('cascade');
            $table->foreign('sigpac_use_id')->references('id')->on('sigpac_use')->onDelete('cascade');

            $table->unique(['plot_id', 'sigpac_use_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_sigpac_use');
    }
};

