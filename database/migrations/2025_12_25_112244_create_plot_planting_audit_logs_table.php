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
        Schema::create('plot_planting_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_planting_id')->constrained('plot_plantings')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // created, updated, deleted
            $table->json('changes')->nullable(); // {old: {...}, new: {...}}
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['plot_planting_id', 'created_at']);
            $table->index('user_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_planting_audit_logs');
    }
};
