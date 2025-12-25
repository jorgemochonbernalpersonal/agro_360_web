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
        Schema::create('agricultural_activity_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('agricultural_activities')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action', 50); // 'created', 'updated', 'deleted', 'locked'
            $table->json('changes')->nullable(); // { old_values: {}, new_values: {} }
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at');
            
            // Índices para búsquedas rápidas
            $table->index(['activity_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });

        // Añadir campo is_locked a agricultural_activities
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('notes');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->foreignId('locked_by')->nullable()->constrained('users')->after('locked_at');
            
            $table->index('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropColumn(['is_locked', 'locked_at', 'locked_by']);
        });
        
        Schema::dropIfExists('agricultural_activity_audit_logs');
    }
};
