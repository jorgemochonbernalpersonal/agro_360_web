<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commercial_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viticulturist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exploitation_id')->nullable()
                ->constrained('exploitations')->onDelete('set null');

            $table->enum('authorization_type', [
                'do_registration',         // Inscripción en Denominación de Origen
                'organic_certification',   // Certificación ecológica
                'planting_right',          // Derecho de plantación
                'replanting_right',        // Derecho de replantación
                'integrated_production',   // Producción Integrada
                'other',
            ]);
            $table->string('authorization_code', 100)->nullable()->comment('Código o número de expediente');
            $table->string('description')->nullable();
            $table->string('issuing_body')->nullable()->comment('Organismo emisor (CCAA, MAPA, Consejo Regulador...)');

            $table->date('issue_date')->comment('Fecha de concesión');
            $table->date('expiry_date')->nullable()->comment('Fecha de caducidad (null = indefinido)');

            $table->string('document_file')->nullable()->comment('Ruta del documento PDF');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['viticulturist_id', 'active']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_authorizations');
    }
};
