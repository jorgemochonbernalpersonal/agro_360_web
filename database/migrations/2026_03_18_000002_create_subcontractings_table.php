<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcontractings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plot_id')->nullable()->constrained('plots')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->enum('service_type', [
                'harvesting',
                'pruning',
                'treatment',
                'fertilization',
                'irrigation',
                'soil_work',
                'transport',
                'analysis',
                'other',
            ])->default('other');
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->date('service_date');
            $table->date('service_end_date')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->boolean('invoiced')->default(false);
            $table->string('invoice_number')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['viticulturist_id', 'campaign_id']);
            $table->index('service_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontractings');
    }
};
