<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residue_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('plot_planting_id')->nullable()->constrained('plot_plantings')->onDelete('set null');
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');

            $table->date('analysis_date')->comment('Fecha del análisis/resultado');
            $table->date('sample_date')->nullable()->comment('Fecha de toma de muestra');
            $table->string('laboratory_name')->comment('Nombre del laboratorio');
            $table->string('laboratory_accreditation', 50)->nullable()->comment('Nº acreditación del laboratorio (ENAC)');
            $table->string('sample_type', 50)->nullable()->comment('Tipo de muestra: uva, mosto, vino');

            // Resultados: array JSON de {product, result_ppb, mrl_ppb, compliant}
            $table->json('results')->nullable()->comment('Array de resultados por producto analizado');
            $table->boolean('overall_compliant')->default(true)->comment('true si todos los resultados cumplen LMR');

            $table->string('certificate_file')->nullable()->comment('Ruta del certificado de análisis PDF');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['campaign_id', 'active']);
            $table->index('analysis_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residue_analyses');
    }
};
