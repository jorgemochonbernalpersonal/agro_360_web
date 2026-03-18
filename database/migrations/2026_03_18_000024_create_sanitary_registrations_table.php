<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sanitary_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('registration_number', 100);
            $table->string('registration_type', 50)->default('rgseaa');
            $table->string('activity_description', 300)->nullable();
            $table->date('registration_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->string('issuing_authority', 200)->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanitary_registrations');
    }
};
