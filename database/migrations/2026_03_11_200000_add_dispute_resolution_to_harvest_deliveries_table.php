<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvest_deliveries', function (Blueprint $table) {
            $table->text('dispute_resolution_note')->nullable()->after('dispute_submitted_at');
            $table->timestamp('dispute_resolved_at')->nullable()->after('dispute_resolution_note');
        });

        // Extend enum to include 'resolved'
        DB::statement("ALTER TABLE harvest_deliveries MODIFY COLUMN status ENUM('pending','matched','disputed','resolved') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Revert enum (existing 'resolved' rows would need manual cleanup first)
        DB::statement("ALTER TABLE harvest_deliveries MODIFY COLUMN status ENUM('pending','matched','disputed') NOT NULL DEFAULT 'pending'");

        Schema::table('harvest_deliveries', function (Blueprint $table) {
            $table->dropColumn(['dispute_resolution_note', 'dispute_resolved_at']);
        });
    }
};
