<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->boolean('disqualified')->default(false)->after('status')
                ->comment('Uva descartada/rechazada (no apta para vinificación)');
            $table->string('disqualified_reason')->nullable()->after('disqualified')
                ->comment('Motivo del descarte');
        });
    }

    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropColumn(['disqualified', 'disqualified_reason']);
        });
    }
};
