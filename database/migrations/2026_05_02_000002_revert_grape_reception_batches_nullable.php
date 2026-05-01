<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revert grape_reception_batches nullable changes.
     * Delete corrupt rows with NULL FK values, change FK action from SET NULL
     * to CASCADE, then restore NOT NULL.
     */
    public function up(): void
    {
        // Delete orphaned/corrupt rows where required FKs are NULL
        DB::table('grape_reception_batches')
            ->whereNull('plot_planting_id')
            ->orWhereNull('campaign_id')
            ->delete();

        Schema::table('grape_reception_batches', function (Blueprint $table) {
            // Drop existing SET NULL foreign keys
            $table->dropForeign(['plot_planting_id']);
            $table->dropForeign(['campaign_id']);

            // Make columns NOT NULL
            $table->unsignedBigInteger('plot_planting_id')->nullable(false)->change();
            $table->unsignedBigInteger('campaign_id')->nullable(false)->change();

            // Re-add foreign keys with CASCADE (compatible with NOT NULL)
            $table->foreign('plot_planting_id')->references('id')->on('plot_plantings')->onDelete('cascade');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations (make fields nullable again with SET NULL).
     */
    public function down(): void
    {
        Schema::table('grape_reception_batches', function (Blueprint $table) {
            $table->dropForeign(['plot_planting_id']);
            $table->dropForeign(['campaign_id']);

            $table->unsignedBigInteger('plot_planting_id')->nullable()->change();
            $table->unsignedBigInteger('campaign_id')->nullable()->change();

            $table->foreign('plot_planting_id')->references('id')->on('plot_plantings')->onDelete('set null');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('set null');
        });
    }
};
