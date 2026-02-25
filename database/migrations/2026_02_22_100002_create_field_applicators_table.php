<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_applicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('set null')
                ->comment('null = aplicador permanente, sin campaña concreta');

            $table->string('name');
            $table->string('ropo_number', 30)
                ->comment('Número ROPO (Registro Oficial de Productores y Operadores) — obligatorio para aplicar fitosanitarios');
            $table->enum('ropo_category', ['basic', 'qualified', 'fumigator', 'pilot'])->default('qualified')
                ->comment('Categoría ROPO: básico, cualificado, fumigador, piloto');
            $table->date('ropo_expiry_date')->nullable()
                ->comment('Fecha de caducidad del carné ROPO');
            $table->boolean('is_advisor')->default(false)
                ->comment('También actúa como asesor técnico');
            $table->string('advisor_license', 50)->nullable()
                ->comment('Número de colegiado/licencia del asesor');
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['viticulturist_id', 'active']);
            $table->index(['viticulturist_id', 'campaign_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_applicators');
    }
};
