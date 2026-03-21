<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvest_byproducts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->date('date');
            $table->enum('byproduct_type', ['orujo', 'raspon', 'lia', 'otro']);
            $table->decimal('quantity_kg', 10, 3);
            $table->enum('destination_type', ['cooperativa', 'bodega', 'destileria', 'compostaje', 'vertedero_autorizado', 'otro']);
            $table->string('destination_name', 255);
            $table->string('document_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvest_byproducts');
    }
};
