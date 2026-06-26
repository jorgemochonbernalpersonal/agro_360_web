<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // security_events.admin_id — queried in admin user history (orWhere admin_id)
        Schema::table('security_events', function (Blueprint $table) {
            $table->index('admin_id');
        });

        // subscriptions.status — filtered in plan/access queries
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index('status');
        });

        // payments.status — filtered in payment processing queries
        Schema::table('payments', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('security_events', function (Blueprint $table) {
            $table->dropIndex(['admin_id']);
        });
    }
};
