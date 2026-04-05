<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winery_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('winery_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('body');
            $table->enum('type', ['info', 'action_required', 'harvest_alert'])->default('info');
            $table->enum('target', ['all', 'specific'])->default('all');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['winery_id', 'published_at']);
        });

        Schema::create('winery_announcement_viticulturist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('winery_announcements')->cascadeOnDelete();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();

            $table->unique(['announcement_id', 'viticulturist_id'], 'announcement_vit_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winery_announcement_viticulturist');
        Schema::dropIfExists('winery_announcements');
    }
};
