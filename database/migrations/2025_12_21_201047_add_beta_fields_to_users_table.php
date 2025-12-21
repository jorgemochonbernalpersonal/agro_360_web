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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_beta_user')->default(true)->after('email_verified_at');
            $table->timestamp('beta_ends_at')->nullable()->after('is_beta_user');
            $table->boolean('beta_access_granted')->default(false)->after('beta_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_beta_user', 'beta_ends_at', 'beta_access_granted']);
        });
    }
};
