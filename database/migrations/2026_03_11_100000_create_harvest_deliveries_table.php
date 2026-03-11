<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvest_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plot_planting_id')->nullable()->constrained('plot_plantings')->nullOnDelete();
            $table->unsignedSmallInteger('vintage_year');
            $table->string('buyer_name');
            $table->decimal('delivered_kg', 10, 2);
            $table->decimal('price_per_kg', 10, 4)->nullable();
            $table->decimal('total_price', 12, 2)->nullable();
            $table->date('delivery_date');
            $table->string('ticket_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvest_deliveries');
    }
};
