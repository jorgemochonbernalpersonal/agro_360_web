<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('do_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('winery_id')->constrained('users')->cascadeOnDelete();

            $table->year('vintage');
            $table->string('batch_number')->nullable();

            $table->unsignedInteger('quantity_requested')->default(0);
            $table->unsignedInteger('quantity_issued')->default(0);
            $table->unsignedInteger('quantity_stock')->default(0);

            $table->enum('status', ['pending', 'approved', 'issued', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['supervisor_id', 'status']);
            $table->index(['supervisor_id', 'vintage']);
            $table->index('winery_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('do_labels');
    }
};
