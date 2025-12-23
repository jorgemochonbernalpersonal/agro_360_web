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
        Schema::create('official_reports', function (Blueprint $table) {
            $table->id();
            
            // Usuario que genera el informe (genérico para cualquier rol)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Tipo de informe
            $table->enum('report_type', [
                'phytosanitary_treatments',      // Tratamientos fitosanitarios
                'full_digital_notebook',         // Cuaderno digital completo
                'pac_report',                    // Informe PAC
                'certification_report',          // Certificado de actividades
                'fertilizations_report',         // Informe de fertilizaciones
                'irrigations_report',            // Informe de riegos
                'harvests_report',               // Informe de cosechas
            ]);
            
            // Periodo del informe
            $table->date('period_start');
            $table->date('period_end');
            
            // FIRMA ELECTRÓNICA
            $table->string('signature_hash', 64)->unique(); // Hash SHA-256 del documento
            $table->timestamp('signed_at');
            $table->ipAddress('signed_ip');
            $table->text('signature_metadata')->nullable(); // JSON con user_agent, device, etc.
            
            // VERIFICACIÓN PÚBLICA
            $table->string('verification_code', 64)->unique(); // Para QR code
            $table->integer('verification_count')->default(0); // Contador de verificaciones
            $table->timestamp('last_verified_at')->nullable();
            
            // METADATA DEL INFORME
            $table->json('report_metadata')->nullable(); // Estadísticas del informe
            $table->string('pdf_path')->nullable(); // Ruta del PDF generado
            $table->integer('pdf_size')->nullable(); // Tamaño en bytes
            $table->string('pdf_filename')->nullable(); // Nombre original del archivo
            
            // AUDITORÍA Y VALIDACIÓN
            $table->boolean('is_valid')->default(true); // Para invalidar si es necesario
            $table->text('invalidation_reason')->nullable();
            $table->timestamp('invalidated_at')->nullable();
            $table->foreignId('invalidated_by')->nullable()->constrained('users');
            
            // PROCESAMIENTO EN COLA
            $table->enum('processing_status', ['pending', 'processing', 'completed', 'failed'])
                ->default('completed')
                ->comment('Estado del procesamiento en cola');
            $table->text('processing_error')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            
            // INDEXES para optimizar queries
            $table->index(['user_id', 'report_type']);
            $table->index(['period_start', 'period_end']);
            $table->index('verification_code');
            $table->index(['is_valid', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('official_reports');
    }
};
