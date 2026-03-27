<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wine_stock_snapshots', function (Blueprint $table) {
            $table->boolean('is_must')->default(false)->after('wine_type');
        });
    }

    public function down(): void
    {
        Schema::table('wine_stock_snapshots', function (Blueprint $table) {
            $table->dropColumn('is_must');
        });
    }
};
