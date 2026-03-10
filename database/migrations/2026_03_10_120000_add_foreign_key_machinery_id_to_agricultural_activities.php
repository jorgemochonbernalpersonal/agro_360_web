<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->foreign('machinery_id')
                ->references('id')
                ->on('machinery')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agricultural_activities', function (Blueprint $table) {
            $table->dropForeign(['machinery_id']);
        });
    }
};
