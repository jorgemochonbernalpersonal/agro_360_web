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
        if (Schema::hasTable('supervisor_winery')) {
            return;
        }

        Schema::create('supervisor_winery', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supervisor_id');
            $table->unsignedBigInteger('winery_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();

            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('winery_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['supervisor_id', 'winery_id']);
            // Note: supervisor_id and winery_id are already indexed by their FKs.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisor_winery');
    }
};

