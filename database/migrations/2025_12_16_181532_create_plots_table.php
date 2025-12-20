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
        if (Schema::hasTable('plots')) {
            return;
        }
        
        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('viticulturist_id')->nullable();
            $table->decimal('area', 10, 3)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('autonomous_community_id');
            $table->unsignedBigInteger('province_id');
            $table->unsignedBigInteger('municipality_id');
            $table->timestamps();
            
            $table->foreign('viticulturist_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('autonomous_community_id')->references('id')->on('autonomous_communities')->onDelete('restrict');
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('restrict');
            $table->foreign('municipality_id')->references('id')->on('municipalities')->onDelete('restrict');
            
            $table->index('viticulturist_id');
            $table->index('active');
            $table->index('municipality_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plots');
    }
};

