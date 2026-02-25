<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');

            $table->string('name')->comment('Nombre descriptivo del documento');
            $table->enum('document_type', [
                'invoice',           // Factura de insumos
                'certificate',       // Certificado (orgánico, DO, etc.)
                'lab_report',        // Informe de laboratorio
                'authorization',     // Autorización
                'map',               // Mapa / croquis
                'analysis',          // Análisis de suelo, agua, etc.
                'other',
            ])->default('other');
            $table->string('file_path')->comment('Ruta del archivo en storage');
            $table->string('original_filename')->nullable()->comment('Nombre original del archivo');
            $table->string('mime_type', 50)->nullable();
            $table->integer('file_size_kb')->nullable()->comment('Tamaño en KB');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['campaign_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_documents');
    }
};
