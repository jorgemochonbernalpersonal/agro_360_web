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
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('crew_member_id')->nullable()->after('crew_id');
            $table->foreign('crew_member_id')->references('id')->on('crew_members')->nullOnDelete();
            $table->index('crew_member_id', 'agri_crew_member_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->dropForeign(['crew_member_id']);
            $table->dropIndex('agri_crew_member_idx');
            $table->dropColumn('crew_member_id');
        });
    }
};


