<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viticultor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticultor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('assigned_by_org_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('cuaderno_access')->default(false);
            $table->timestamp('cuaderno_granted_at')->nullable();
            $table->timestamp('cuaderno_revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['viticultor_id', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viticultor_assignments');
    }
};
