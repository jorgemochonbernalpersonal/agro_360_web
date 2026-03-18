<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plot_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plot_id')->nullable()->constrained('plots')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->enum('category', [
                'labor',
                'machinery',
                'materials',
                'phytosanitary',
                'fertilizer',
                'water',
                'insurance',
                'transport',
                'subcontracting',
                'other',
            ])->default('other');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('cost_date');
            $table->string('supplier')->nullable();
            $table->string('invoice_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['viticulturist_id', 'campaign_id']);
            $table->index(['viticulturist_id', 'plot_id']);
            $table->index('cost_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plot_costs');
    }
};
