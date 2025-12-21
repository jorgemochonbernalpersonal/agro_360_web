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
        Schema::table('sigpac_code', function (Blueprint $table) {
            $table->string('code_autonomous_community', 10)->nullable()->after('id');
            $table->index('code_autonomous_community');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sigpac_code', function (Blueprint $table) {
            $table->dropIndex(['code_autonomous_community']);
            $table->dropColumn('code_autonomous_community');
        });
    }
};
