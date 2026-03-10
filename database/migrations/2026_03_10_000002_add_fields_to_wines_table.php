<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wines', function (Blueprint $table) {
            $table->boolean('is_must')->default(false)->after('user_id');
            $table->boolean('is_organic')->default(false)->after('is_must');
            $table->string('aging_type', 50)->nullable()->after('wine_type');
            $table->string('category', 50)->nullable()->after('aging_type');
            $table->foreignId('oenologist_id')->nullable()->after('user_id')
                ->constrained('oenologists')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wines', function (Blueprint $table) {
            $table->dropForeign(['oenologist_id']);
            $table->dropColumn(['is_must', 'is_organic', 'aging_type', 'category', 'oenologist_id']);
        });
    }
};
