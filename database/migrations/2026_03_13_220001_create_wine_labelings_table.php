<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wine_labelings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wine_bottling_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('label_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->date('labeling_date');
            $table->unsignedInteger('quantity_labeled');
            $table->unsignedBigInteger('from_number')->nullable();
            $table->unsignedBigInteger('to_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_labelings');
    }
};
