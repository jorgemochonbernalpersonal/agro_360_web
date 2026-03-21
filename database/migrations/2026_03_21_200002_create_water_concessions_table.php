<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_concessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->enum('concession_type', ['superficial', 'subterranea', 'comunidad_regantes', 'otro']);
            $table->string('concession_number', 100)->nullable();
            $table->string('water_body', 255);
            $table->string('authority', 255);
            $table->date('concession_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('max_volume_m3', 12, 3);
            $table->decimal('used_volume_m3', 12, 3)->nullable();
            $table->decimal('surface_ha', 8, 4)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_concessions');
    }
};
