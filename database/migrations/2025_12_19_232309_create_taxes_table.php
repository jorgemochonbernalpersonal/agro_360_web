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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('IVA, IGIC, etc.');
            $table->string('code')->comment('IVA, IGIC');
            $table->decimal('rate', 5, 2)->comment('Tasa porcentual (21.00, 7.00, etc.)');
            $table->string('region')->nullable()->comment('España, Canarias, etc.');
            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            $table->index('code');
            $table->index('active');
            $table->unique(['code', 'rate', 'region'], 'tax_code_rate_region_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
