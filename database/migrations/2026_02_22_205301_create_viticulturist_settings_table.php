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
        Schema::create('viticulturist_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->cascadeOnDelete();

            // Defaults para nuevas plantaciones
            $table->decimal('default_limit_kg_per_ha', 8, 2)->nullable()
                ->comment('Límite kg/ha por defecto para nuevas plantaciones');
            $table->decimal('degree_day_base', 4, 1)->default(10.0)
                ->comment('Temperatura base para cálculo de grados-día (°C)');

            // Prefijos de numeración del cuaderno
            $table->string('document_prefix_activity', 20)->default('ACT')
                ->comment('Prefijo numeración actividades');
            $table->string('document_prefix_harvest', 20)->default('VND')
                ->comment('Prefijo numeración vendimias');

            // Pie de página legal en PDFs
            $table->text('legal_text_fieldbook')->nullable()
                ->comment('Texto legal al pie del PDF del cuaderno');

            // Notificaciones
            $table->boolean('notify_harvest_alerts')->default(true);
            $table->boolean('notify_activity_alerts')->default(true);

            $table->timestamps();

            $table->unique('viticulturist_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viticulturist_settings');
    }
};
