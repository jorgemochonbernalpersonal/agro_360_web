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
        Schema::table('phytosanitary_treatments', function (Blueprint $table) {
            $table->foreignId('pest_id')->nullable()->after('product_id')->constrained('pests')->onDelete('set null');
            $table->index('pest_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phytosanitary_treatments', function (Blueprint $table) {
            $table->dropForeign(['pest_id']);
            $table->dropColumn('pest_id');
        });
    }
};
