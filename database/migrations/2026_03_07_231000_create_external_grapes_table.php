<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_grapes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // winery
            $table->string('supplier_name');
            $table->enum('grape_type', ['grapes', 'must', 'bulk_wine'])->default('grapes');
            $table->foreignId('grape_variety_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('color', ['white', 'red', 'rose', 'other'])->nullable();
            $table->string('protection_level')->nullable(); // DO, IGP, vino de mesa, etc.
            $table->string('geographic_origin')->nullable();
            $table->unsignedSmallInteger('vintage_year')->nullable();
            $table->decimal('alcohol_pct', 4, 2)->nullable();
            $table->decimal('total_weight_kg', 10, 3);
            $table->decimal('used_weight_kg', 10, 3)->default(0);
            $table->date('entry_date');
            $table->date('harvest_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->foreignId('container_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->enum('status', ['available', 'used', 'archived'])->default('available');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'vintage_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_grapes');
    }
};
