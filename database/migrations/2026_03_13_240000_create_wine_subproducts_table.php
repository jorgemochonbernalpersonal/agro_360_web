<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_subproducts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wine_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30); // orujo, lias, vinaza, other
            $table->date('subproduct_date');
            $table->decimal('quantity', 10, 3);
            $table->foreignId('unit_of_measurement_id')->nullable()->constrained('units_of_measurement')->nullOnDelete();
            $table->string('destination', 30); // distillery, authorized_plant, own_use, other
            $table->string('destination_name', 200)->nullable();
            $table->string('lot_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['user_id', 'subproduct_date']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_subproducts');
    }
};
