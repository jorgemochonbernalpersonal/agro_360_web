<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phenology_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_planting_id')->constrained('plot_plantings')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');

            $table->enum('event', [
                'budbreak',     // Desborre
                'shoot_growth', // Crecimiento del pámpano
                'flowering',    // Floración
                'fruit_set',    // Cuajado
                'veraison',     // Envero
                'pre_harvest',  // Pre-vendimia
                'harvest',      // Vendimia
            ])->comment('Estadio fenológico observado');

            $table->date('obs_date')->comment('Fecha de la observación');
            $table->enum('source', ['manual', 'sensor', 'model', 'auto'])->default('manual')
                ->comment('Origen del dato: manual=observación en campo, sensor=sensor IoT, model=modelo predictivo, auto=satélite');
            $table->tinyInteger('confidence')->default(100)
                ->comment('Nivel de confianza 0-100 (100=certeza absoluta)');
            $table->decimal('degree_days_accumulated', 8, 2)->nullable()
                ->comment('Grados-día acumulados desde brotación hasta este evento (suma de temperaturas > base)');
            $table->integer('bbch_code')->nullable()
                ->comment('Código BBCH correspondiente al estadio (escala internacional)');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Una sola observación por evento, plantación y campaña (puede actualizarse)
            $table->unique(['plot_planting_id', 'campaign_id', 'event'], 'unique_phenology_event');
            $table->index(['plot_planting_id', 'campaign_id']);
            $table->index('obs_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phenology_observations');
    }
};
