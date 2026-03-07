<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimated_yields', function (Blueprint $table) {
            $table->string('health_status')->nullable()->after('health_percentage');
            $table->boolean('other_wineries')->default(false)->after('health_status');
        });
    }

    public function down(): void
    {
        Schema::table('estimated_yields', function (Blueprint $table) {
            $table->dropColumn(['health_status', 'other_wineries']);
        });
    }
};
