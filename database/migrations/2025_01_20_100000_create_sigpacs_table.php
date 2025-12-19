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
        Schema::create('sigpacs', function (Blueprint $table) {
            $table->id();
            $table->string('code_polygon', 10)->nullable();
            $table->string('code_plot', 10)->nullable();
            $table->string('code_enclosure', 10)->nullable();
            $table->string('code_aggregate', 10)->nullable();
            $table->string('code_province', 10)->nullable();
            $table->string('code_zone', 10)->nullable();
            $table->string('code', 30)->nullable();
            $table->string('code_municipality', 10);
            $table->timestamps();
            
            $table->index('code');
            $table->index('code_municipality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sigpacs');
    }
};

