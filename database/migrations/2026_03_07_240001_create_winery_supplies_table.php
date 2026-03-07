<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winery_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('commercial_name')->nullable();
            $table->enum('supply_type', [
                'cleaning', 'sulfiting', 'fining', 'filtration',
                'yeast', 'nutrient', 'enzyme', 'tannin', 'acid',
                'sugar', 'analysis', 'packaging', 'other',
            ])->default('other');
            $table->foreignId('unit_of_measurement_id')->nullable()->constrained('units_of_measurement')->nullOnDelete();
            $table->decimal('current_stock', 12, 3)->nullable();
            $table->decimal('min_stock_alert', 12, 3)->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'active']);
            $table->index(['user_id', 'supply_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winery_supplies');
    }
};
