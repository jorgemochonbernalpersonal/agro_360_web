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
        Schema::table('wine_bottlings', function (Blueprint $table) {
            $table->foreignId('container_id')
                ->nullable()
                ->after('wine_id')
                ->constrained('containers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wine_bottlings', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Container::class);
            $table->dropColumn('container_id');
        });
    }
};
