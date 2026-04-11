<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('do_labels', function (Blueprint $table) {
            $table->foreignId('supervisor_request_id')
                ->nullable()
                ->after('winery_id')
                ->constrained('supervisor_requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('do_labels', function (Blueprint $table) {
            $table->dropForeignIfExists(['supervisor_request_id']);
            $table->dropColumn('supervisor_request_id');
        });
    }
};
