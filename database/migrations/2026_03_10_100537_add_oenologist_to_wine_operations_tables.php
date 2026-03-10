<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wine_analyses', function (Blueprint $table) {
            $table->foreignId('oenologist_id')->nullable()->after('container_id')
                ->constrained('oenologists')->nullOnDelete();
        });

        Schema::table('wine_transfers', function (Blueprint $table) {
            $table->foreignId('oenologist_id')->nullable()->after('created_by')
                ->constrained('oenologists')->nullOnDelete();
        });

        Schema::table('wine_process_details', function (Blueprint $table) {
            $table->foreignId('oenologist_id')->nullable()->after('created_by')
                ->constrained('oenologists')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wine_analyses', function (Blueprint $table) {
            $table->dropForeign(['oenologist_id']);
            $table->dropColumn('oenologist_id');
        });

        Schema::table('wine_transfers', function (Blueprint $table) {
            $table->dropForeign(['oenologist_id']);
            $table->dropColumn('oenologist_id');
        });

        Schema::table('wine_process_details', function (Blueprint $table) {
            $table->dropForeign(['oenologist_id']);
            $table->dropColumn('oenologist_id');
        });
    }
};
