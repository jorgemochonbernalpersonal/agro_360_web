<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('security_events') && !$this->hasIndex('security_events', 'security_events_admin_id_index')) {
            Schema::table('security_events', function (Blueprint $table) {
                $table->index('admin_id');
            });
        }

        if (!$this->hasIndex('subscriptions', 'subscriptions_status_index')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->index('status');
            });
        }

        if (!$this->hasIndex('payments', 'payments_status_index')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasIndex('payments', 'payments_status_index')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex(['status']);
            });
        }

        if ($this->hasIndex('subscriptions', 'subscriptions_status_index')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('security_events') && $this->hasIndex('security_events', 'security_events_admin_id_index')) {
            Schema::table('security_events', function (Blueprint $table) {
                $table->dropIndex(['admin_id']);
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
