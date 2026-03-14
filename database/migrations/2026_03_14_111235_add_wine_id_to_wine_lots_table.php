<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wine_lots', function (Blueprint $table) {
            $table->foreignId('wine_id')
                ->nullable()
                ->after('user_id')
                ->constrained('wines')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wine_lots', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Wine::class);
            $table->dropColumn('wine_id');
        });
    }
};
