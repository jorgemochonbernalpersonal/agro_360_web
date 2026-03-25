<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supervisor_viticulturist', function (Blueprint $table) {
            $table->boolean('cuaderno_access')->default(false)->after('assigned_by');
            $table->timestamp('cuaderno_granted_at')->nullable()->after('cuaderno_access');
            $table->timestamp('cuaderno_revoked_at')->nullable()->after('cuaderno_granted_at');
        });
    }

    public function down(): void
    {
        Schema::table('supervisor_viticulturist', function (Blueprint $table) {
            $table->dropColumn(['cuaderno_access', 'cuaderno_granted_at', 'cuaderno_revoked_at']);
        });
    }
};
