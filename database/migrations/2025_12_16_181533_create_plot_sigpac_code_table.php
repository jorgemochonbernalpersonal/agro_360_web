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
        Schema::create('plot_sigpac_code', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plot_id');
            $table->unsignedBigInteger('sigpac_code_id');
            $table->timestamps();

            $table->foreign('plot_id')->references('id')->on('plots')->onDelete('cascade');
            $table->foreign('sigpac_code_id')->references('id')->on('sigpac_code')->onDelete('cascade');

            $table->unique(['plot_id', 'sigpac_code_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_sigpac_code');
    }
};

