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
            $table->decimal('ndvi_alert_threshold', 3, 2)->default(0.30)->after('municipality_id');
            $table->boolean('alert_email_enabled')->default(false)->after('ndvi_alert_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropColumn(['ndvi_alert_threshold', 'alert_email_enabled']);
        });
    }
};
