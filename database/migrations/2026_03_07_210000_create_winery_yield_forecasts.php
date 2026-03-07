<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winery_yield_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('winery_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plot_planting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('vintage_year');
            $table->decimal('estimated_kg', 10, 3);
            $table->date('estimation_date');
            $table->enum('status', ['draft', 'confirmed'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['winery_id', 'plot_planting_id', 'campaign_id'],
                'wyf_unique_winery_planting_campaign'
            );
            $table->index(['winery_id', 'vintage_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winery_yield_forecasts');
    }
};
