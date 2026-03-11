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
        Schema::table('harvest_deliveries', function (Blueprint $table) {
            $table->time('harvest_time')->nullable()->after('delivery_date');
            $table->boolean('disqualified')->default(false)->after('notes');
            $table->string('disqualified_reason', 500)->nullable()->after('disqualified');
            $table->decimal('baume_degree', 5, 2)->nullable()->after('disqualified_reason');
            $table->decimal('brix_degree', 5, 2)->nullable()->after('baume_degree');
            $table->decimal('potential_alcohol', 5, 2)->nullable()->after('brix_degree');
            $table->decimal('acidity_level', 5, 2)->nullable()->after('potential_alcohol');
            $table->decimal('ph_level', 4, 2)->nullable()->after('acidity_level');
        });
    }

    public function down(): void
    {
        Schema::table('harvest_deliveries', function (Blueprint $table) {
            $table->dropColumn([
                'harvest_time', 'disqualified', 'disqualified_reason',
                'baume_degree', 'brix_degree', 'potential_alcohol', 'acidity_level', 'ph_level',
            ]);
        });
    }
};
