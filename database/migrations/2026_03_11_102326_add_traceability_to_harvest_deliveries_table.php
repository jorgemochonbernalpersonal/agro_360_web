<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvest_deliveries', function (Blueprint $table) {
            $table->string('destination_rega_code', 20)->nullable()->after('ticket_number');
            $table->string('vehicle_plate', 20)->nullable()->after('destination_rega_code');
        });
    }

    public function down(): void
    {
        Schema::table('harvest_deliveries', function (Blueprint $table) {
            $table->dropColumn(['destination_rega_code', 'vehicle_plate']);
        });
    }
};
