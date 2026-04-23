<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wine_transfers', function (Blueprint $table) {
            $table->foreignId('source_wine_id')
                ->nullable()
                ->after('wine_id')
                ->constrained('wines')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wine_transfers', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Wine::class, 'source_wine_id');
            $table->dropColumn('source_wine_id');
        });
    }
};
