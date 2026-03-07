<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Opción B: desacoplamiento bodega ↔ viticultor.
 *
 * - Crea grape_reception_batches: acumulador de kg por (bodega + plantación + campaña).
 * - Modifica harvests:
 *     · activity_id → nullable (las recepciones de bodega no tienen actividad del viticultor)
 *     · +winery_id  → FK nullable a users (propietario bodega de la recepción)
 *     · +batch_id   → FK nullable a grape_reception_batches
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla acumuladora de recepciones por bodega + plantación + campaña
        Schema::create('grape_reception_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('winery_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('viticulturist_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('plot_planting_id')
                ->constrained('plot_plantings')
                ->onDelete('cascade');

            $table->foreignId('campaign_id')
                ->constrained('campaigns')
                ->onDelete('cascade');

            $table->smallInteger('vintage_year');

            // Acumulado (se actualiza con cada GrapeReception)
            $table->decimal('total_weight_kg', 12, 3)->default(0);

            // Info DO leída de la plantación al crear el batch
            $table->string('designation_of_origin')->nullable();

            // Estado del batch
            $table->enum('status', ['open', 'closed', 'invoiced'])->default('open');

            $table->text('notes')->nullable();
            $table->timestamps();

            // Una sola entrada por bodega + plantación + campaña
            $table->unique(['winery_id', 'plot_planting_id', 'campaign_id'], 'grb_unique_winery_planting_campaign');

            $table->index(['winery_id', 'vintage_year']);
            $table->index(['viticulturist_id', 'vintage_year']);
        });

        // 2. Modificar harvests
        Schema::table('harvests', function (Blueprint $table) {
            // Eliminar FK existente sobre activity_id para hacerla nullable
            $table->dropForeign(['activity_id']);

            // Hacer activity_id nullable
            $table->unsignedBigInteger('activity_id')->nullable()->change();

            // Re-añadir FK nullable
            $table->foreign('activity_id')
                ->references('id')
                ->on('agricultural_activities')
                ->onDelete('set null');

            // Propietario bodega (solo para recepciones de bodega)
            $table->foreignId('winery_id')
                ->nullable()
                ->after('activity_id')
                ->constrained('users')
                ->onDelete('set null');

            // Lote acumulador
            $table->foreignId('batch_id')
                ->nullable()
                ->after('winery_id')
                ->constrained('grape_reception_batches')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');

            $table->dropForeign(['winery_id']);
            $table->dropColumn('winery_id');

            $table->dropForeign(['activity_id']);
            $table->unsignedBigInteger('activity_id')->nullable(false)->change();
            $table->foreign('activity_id')
                ->references('id')
                ->on('agricultural_activities')
                ->onDelete('cascade');
        });

        Schema::dropIfExists('grape_reception_batches');
    }
};
