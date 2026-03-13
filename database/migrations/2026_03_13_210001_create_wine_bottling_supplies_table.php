<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_bottling_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_bottling_id')->constrained()->cascadeOnDelete();
            $table->foreignId('winery_supply_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supply_name', 255);
            $table->decimal('quantity', 10, 3);
            $table->foreignId('unit_of_measurement_id')->nullable()->constrained('units_of_measurement')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_bottling_supplies');
    }
};
