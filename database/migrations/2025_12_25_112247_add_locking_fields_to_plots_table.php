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
        Schema::table('plots', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('active');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null')->after('locked_at');
            $table->string('lock_reason')->nullable()->after('locked_by');
            
            $table->index('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropIndex(['is_locked']);
            $table->dropColumn(['is_locked', 'locked_at', 'locked_by', 'lock_reason']);
        });
    }
};
