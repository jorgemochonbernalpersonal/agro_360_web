<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // winery owner
            $table->string('category');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('cost_date');
            $table->string('supplier')->nullable();
            $table->string('invoice_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['user_id', 'wine_id']);
            $table->index(['wine_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_costs');
    }
};
