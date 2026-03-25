<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notebook_access_requests', function (Blueprint $table) {
            // Make winery_id nullable so supervisor-only requests don't need it
            $table->unsignedBigInteger('winery_id')->nullable()->change();

            // Add supervisor_id for DO-originated requests
            $table->foreignId('supervisor_id')
                ->nullable()
                ->after('winery_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Unique per supervisor + viticulturist pair
            $table->unique(['supervisor_id', 'viticulturist_id'], 'nar_supervisor_viticulturist_unique');
        });
    }

    public function down(): void
    {
        Schema::table('notebook_access_requests', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropUnique('nar_supervisor_viticulturist_unique');
            $table->dropColumn('supervisor_id');
            $table->unsignedBigInteger('winery_id')->nullable(false)->change();
        });
    }
};
