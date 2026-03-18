<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_stock_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wine_id')->constrained('wines')->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->decimal('quantity_liters', 12, 3)->default(0);
            $table->unsignedSmallInteger('container_count')->default(0);
            $table->decimal('alcohol_percentage', 5, 2)->nullable();
            $table->string('vintage', 4)->nullable();
            $table->string('wine_type', 30)->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'snapshot_date', 'wine_id']);
            $table->index(['user_id', 'snapshot_date']);
            $table->index('wine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_stock_snapshots');
    }
};
