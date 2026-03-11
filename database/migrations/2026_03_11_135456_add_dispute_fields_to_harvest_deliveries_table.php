<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvest_deliveries', function (Blueprint $table) {
            $table->text('dispute_note')->nullable()->after('discrepancy_kg');
            $table->timestamp('dispute_submitted_at')->nullable()->after('dispute_note');
        });
    }

    public function down(): void
    {
        Schema::table('harvest_deliveries', function (Blueprint $table) {
            $table->dropColumn(['dispute_note', 'dispute_submitted_at']);
        });
    }
};
