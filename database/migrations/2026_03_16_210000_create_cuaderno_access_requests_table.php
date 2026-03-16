<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuaderno_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('winery_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['winery_id', 'viticulturist_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuaderno_access_requests');
    }
};
