<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();
            $table->enum('certification_type', [
                'ecologico', 'produccion_integrada', 'globalgap',
                'rainforest', 'denominacion_origen', 'indicacion_geografica', 'otro',
            ]);
            $table->string('certifying_body', 255);
            $table->string('certificate_number', 100)->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->string('scope', 500)->nullable();
            $table->date('audit_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
