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
        Schema::create('winery_viticulturist', function (Blueprint $table) {
            $table->id();
            $table->integer('winery_id');
            $table->integer('viticulturist_id');
            $table->integer('assigned_by')->nullable();
            $table->string('source', 50)->default('own'); // 'own', 'supervisor', 'viticulturist'
            $table->integer('supervisor_id')->nullable();
            $table->integer('parent_viticulturist_id')->nullable();
            $table->timestamps();

            $table->foreign('winery_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('viticulturist_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('parent_viticulturist_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['winery_id', 'viticulturist_id']);
            
            $table->index('winery_id');
            $table->index('viticulturist_id');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winery_viticulturist');
    }
};
