<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plot_environments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('plot_id')->constrained('plots')->onDelete('cascade');
            $table->foreignId('plot_planting_id')->nullable()
                ->constrained('plot_plantings')->onDelete('set null');
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');

            // Captaciones de agua
            $table->boolean('water_intake_nearby')->default(false)->comment('¿Hay captación de agua cercana?');
            $table->decimal('water_intake_distance_m', 8, 2)->nullable()->comment('Distancia a captación (m)');

            // Zonas protegidas
            $table->boolean('protected_zone_total')->default(false)->comment('Zona de exclusión total');
            $table->boolean('protected_zone_partial')->default(false)->comment('Zona de exclusión parcial');
            $table->string('protection_zone_type', 100)->nullable()->comment('Tipo: N2000, LIC, ZEPA...');
            $table->decimal('buffer_zone_m', 8, 2)->nullable()->comment('Zona tampón requerida (m)');

            // Pendiente y escorrentía
            $table->decimal('slope_pct', 5, 2)->nullable()->comment('Pendiente media de la parcela (%)');
            $table->boolean('erosion_risk')->default(false)->comment('Riesgo de erosión significativo');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'plot_id'], 'unique_campaign_plot_env');
            $table->index(['viticulturist_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plot_environments');
    }
};
