<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agri_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->string('policy_number')->nullable();
            $table->string('insurance_company');
            $table->enum('coverage_type', [
                'frost',
                'hail',
                'drought',
                'flood',
                'fire',
                'pest',
                'comprehensive',
                'other',
            ])->default('comprehensive');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('insured_amount', 12, 2)->nullable();
            $table->decimal('premium', 10, 2)->nullable();
            $table->decimal('subsidy_amount', 10, 2)->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending'])->default('pending');
            $table->string('agent_name')->nullable();
            $table->string('agent_phone')->nullable();
            $table->text('covered_plots')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['viticulturist_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agri_insurances');
    }
};
