<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bottling_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('authorization_number', 100);
            $table->string('authorization_type', 50)->default('standard');
            $table->foreignId('wine_id')->nullable()->constrained('wines')->nullOnDelete();
            $table->decimal('authorized_volume_liters', 12, 2)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('issuing_authority', 200)->nullable();
            $table->string('status', 20)->default('active');
            $table->text('conditions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bottling_authorizations');
    }
};
