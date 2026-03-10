<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_additives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_id')->constrained('wines')->cascadeOnDelete();
            $table->foreignId('wine_process_detail_id')->nullable()->constrained('wine_process_details')->nullOnDelete();
            $table->foreignId('winery_supply_id')->nullable()->constrained('winery_supplies')->nullOnDelete();
            $table->foreignId('oenologist_id')->nullable()->constrained('oenologists')->nullOnDelete();
            $table->foreignId('unit_of_measurement_id')->nullable()->constrained('units_of_measurement')->nullOnDelete();
            $table->string('additive_name', 200);         // nombre libre (puede diferir del supply)
            $table->decimal('quantity', 12, 3);
            $table->date('application_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['wine_id', 'application_date']);
            $table->index('wine_process_detail_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_additives');
    }
};
