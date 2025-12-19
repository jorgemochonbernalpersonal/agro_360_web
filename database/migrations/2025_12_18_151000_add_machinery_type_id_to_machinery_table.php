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
        Schema::table('machinery', function (Blueprint $table) {
            $table->unsignedBigInteger('machinery_type_id')->nullable()->after('type');
            $table->foreign('machinery_type_id')->references('id')->on('machinery_types')->nullOnDelete();
            $table->index('machinery_type_id', 'machinery_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machinery', function (Blueprint $table) {
            $table->dropForeign(['machinery_type_id']);
            $table->dropIndex('machinery_type_idx');
            $table->dropColumn('machinery_type_id');
        });
    }
};


