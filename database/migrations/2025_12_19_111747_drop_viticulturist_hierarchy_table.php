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
        Schema::dropIfExists('viticulturist_hierarchy');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('viticulturist_hierarchy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_viticulturist_id');
            $table->unsignedBigInteger('child_viticulturist_id');
            $table->unsignedBigInteger('winery_id')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();

            $table->foreign('parent_viticulturist_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('child_viticulturist_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('winery_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['parent_viticulturist_id', 'child_viticulturist_id', 'winery_id']);
            $table->check('parent_viticulturist_id <> child_viticulturist_id');
        });
    }
};
