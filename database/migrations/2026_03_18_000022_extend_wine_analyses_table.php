<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wine_analyses', function (Blueprint $table) {
            // Owner reference (winery user) — allows standalone analyses not linked to a specific wine
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();

            // Make wine_id nullable so analyses can exist without a linked wine record
            $table->foreignId('wine_id')->nullable()->change();

            // Rename laboratory_name → laboratory (keep old column for migration safety, add new one)
            $table->string('laboratory', 200)->nullable()->after('user_id');

            // Sample reference field
            $table->string('sample_reference', 100)->nullable()->after('laboratory');

            // Rename alcohol → alcoholic_strength (add new column; old kept for backward compat)
            $table->decimal('alcoholic_strength', 5, 2)->nullable()->after('sample_reference');

            // SO₂ columns with new names (old so2_free / so2_total kept for backward compat)
            $table->decimal('free_so2', 6, 1)->nullable()->after('alcoholic_strength');
            $table->decimal('total_so2', 6, 1)->nullable()->after('free_so2');

            // Conformance result
            $table->string('result', 20)->default('pending')->after('notes');

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('wine_analyses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn([
                'user_id',
                'laboratory',
                'sample_reference',
                'alcoholic_strength',
                'free_so2',
                'total_so2',
                'result',
            ]);
        });
    }
};
