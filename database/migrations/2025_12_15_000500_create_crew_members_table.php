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
        Schema::create('crew_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crew_id'); // Se hará nullable después
            $table->unsignedBigInteger('viticulturist_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
            
            $table->foreign('crew_id')->references('id')->on('crews')->onDelete('cascade');
            $table->foreign('viticulturist_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('crew_id');
            $table->index('viticulturist_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_members');
    }
};

