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
        Schema::create('supervisor_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('winery_id')->constrained('users')->cascadeOnDelete();

            $table->string('type', 50);
            // label_request | qualification | nonconformity | harvest_declaration | certification

            $table->enum('status', [
                'draft', 'pending', 'in_review', 'approved', 'rejected', 'archived',
            ])->default('draft');

            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->text('response_notes')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['supervisor_id', 'status']);
            $table->index(['winery_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisor_requests');
    }
};
