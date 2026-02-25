<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_id')->constrained('supplies')->onDelete('cascade');
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('set null');

            $table->date('invoice_date');
            $table->string('invoice_number', 100)->nullable();
            $table->decimal('quantity', 10, 3)->comment('Cantidad comprada');
            $table->string('unit_of_measurement', 20)->comment('L, kg, g, mL, ud');
            $table->decimal('price_per_unit', 10, 4)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->string('supplier_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['supply_id']);
            $table->index(['viticulturist_id', 'invoice_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_purchases');
    }
};
