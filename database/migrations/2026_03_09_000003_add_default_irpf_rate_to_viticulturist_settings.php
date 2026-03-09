<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viticulturist_settings', function (Blueprint $table) {
            $table->decimal('default_irpf_rate', 5, 2)->default(0)->after('notify_activity_alerts');
        });
    }

    public function down(): void
    {
        Schema::table('viticulturist_settings', function (Blueprint $table) {
            $table->dropColumn('default_irpf_rate');
        });
    }
};
