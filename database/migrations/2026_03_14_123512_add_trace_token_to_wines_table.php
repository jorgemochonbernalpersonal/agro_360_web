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
        Schema::table('wines', function (Blueprint $table) {
            $table->uuid('trace_token')->nullable()->unique()->after('internal_code');
        });
    }

    public function down(): void
    {
        Schema::table('wines', function (Blueprint $table) {
            $table->dropColumn('trace_token');
        });
    }
};
