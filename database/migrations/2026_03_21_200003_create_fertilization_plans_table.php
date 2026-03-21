<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fertilization_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->smallInteger('plan_year');
            $table->boolean('nitrate_zone')->default(false);
            $table->string('prepared_by', 255)->nullable();
            $table->date('approval_date')->nullable();
            $table->decimal('total_surface_ha', 8, 4)->nullable();
            $table->decimal('total_n_kg_ha', 8, 3)->nullable();
            $table->decimal('total_p_kg_ha', 8, 3)->nullable();
            $table->decimal('total_k_kg_ha', 8, 3)->nullable();
            $table->json('plan_lines')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fertilization_plans');
    }
};
