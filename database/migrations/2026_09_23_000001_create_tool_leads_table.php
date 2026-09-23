<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_leads', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('profile', 32)->nullable();
            $table->string('tool', 64)->index();
            $table->json('context')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('consented_at');
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();

            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_leads');
    }
};
