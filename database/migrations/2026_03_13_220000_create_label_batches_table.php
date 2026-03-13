<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('label_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wine_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 255);
            $table->enum('source', ['own', 'do_assigned', 'other'])->default('own');
            $table->unsignedBigInteger('start_number');
            $table->unsignedBigInteger('end_number');
            $table->unsignedInteger('total_quantity');      // stored: end - start + 1
            $table->unsignedInteger('used_quantity')->default(0);
            $table->unsignedInteger('wasted_quantity')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('label_batches');
    }
};
