<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained()->cascadeOnDelete();
            $table->enum('maintenance_type', [
                'cleaning', 'sulfuring', 'inspection',
                'repair', 'tartrate_removal', 'other',
            ])->default('cleaning');
            $table->string('maintenance_name');
            $table->date('scheduled_date');
            $table->date('performed_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('performed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['container_id', 'status']);
            $table->index(['container_id', 'scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_maintenances');
    }
};
