<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advisory_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('set null')
                ->comment('null = asesor permanente, no ligado a campaña concreta');

            $table->string('advisor_name');
            $table->string('license_number', 50)->comment('Nº colegiado / licencia del asesor');
            $table->enum('specialty', [
                'phytosanitary',    // Asesor fitosanitario (obligatorio PI)
                'agronomy',         // Agronomía general
                'oenology',         // Enología
                'sustainability',   // Sostenibilidad / certificaciones
                'other',
            ])->default('phytosanitary');
            $table->string('company_name')->nullable()->comment('Empresa/asesoría');
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['viticulturist_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advisory_memberships');
    }
};
