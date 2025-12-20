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
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->unsignedBigInteger('autonomous_community_id');
            $table->timestamps();
            
            $table->foreign('autonomous_community_id')->references('id')->on('autonomous_communities')->onDelete('cascade');
            $table->index('code');
            $table->index('autonomous_community_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};

