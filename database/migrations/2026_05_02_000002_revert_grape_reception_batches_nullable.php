<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revert grape_reception_batches nullable changes.
     * Delete corrupt rows with NULL FK values, then restore NOT NULL.
     */
    public function up(): void
    {
        // Delete orphaned/corrupt rows where required FKs are NULL
        DB::table('grape_reception_batches')
            ->whereNull('plot_planting_id')
            ->orWhereNull('campaign_id')
            ->delete();

        Schema::table('grape_reception_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('plot_planting_id')->nullable(false)->change();
            $table->unsignedBigInteger('campaign_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations (make fields nullable again).
     */
    public function down(): void
    {
        Schema::table('grape_reception_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('plot_planting_id')->nullable()->change();
            $table->unsignedBigInteger('campaign_id')->nullable()->change();
        });
    }
};
