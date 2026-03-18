<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cellar_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('operation_type', 50); // racking, clarification, filtration, stabilization, sulfiting, blending, other
            $table->date('operation_date');
            $table->foreignId('source_container_id')->nullable()->constrained('containers')->nullOnDelete();
            $table->foreignId('target_container_id')->nullable()->constrained('containers')->nullOnDelete();
            $table->decimal('volume_liters', 12, 2)->nullable();
            $table->string('responsible_person', 150)->nullable();
            $table->string('status', 20)->default('planned'); // planned, in_progress, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cellar_operations');
    }
};
