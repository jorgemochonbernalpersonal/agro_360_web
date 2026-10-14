<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índice compuesto para las consultas del componente Notifications
     * (dropdown de campana): notifications() hace latest() sobre
     * notifiable_type+notifiable_id, y unreadNotifications() añade
     * whereNull('read_at'). morphs('notifiable') ya indexa (type, id) pero
     * sin read_at/created_at, así que esas dos consultas siguen escaneando
     * todas las filas del usuario antes de ordenar/filtrar.
     */
    public function up(): void
    {
        if (Schema::hasTable('notifications') && ! $this->hasIndex('notifications', 'notifications_composite_idx')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['notifiable_type', 'notifiable_id', 'read_at', 'created_at'], 'notifications_composite_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notifications') && $this->hasIndex('notifications', 'notifications_composite_idx')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex('notifications_composite_idx');
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        return count($indexes) > 0;
    }
};
