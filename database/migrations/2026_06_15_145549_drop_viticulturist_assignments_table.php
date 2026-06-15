<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('viticulturist_assignments');
    }

    public function down(): void
    {
        Schema::create('viticulturist_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('assigned_by_org_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('notebook_access')->default(false);
            $table->timestamp('notebook_granted_at')->nullable();
            $table->timestamp('notebook_revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['viticulturist_id', 'organization_id']);
        });
    }
};
