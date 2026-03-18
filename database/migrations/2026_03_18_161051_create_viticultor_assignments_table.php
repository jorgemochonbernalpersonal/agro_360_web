<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asignaciones de viticultor a organizaciones (DO / Bodega).
 *
 * Separa la jerarquía de asignación por organizaciones del pivot
 * winery_viticulturist (que opera a nivel de usuarios).
 * Permite que un DO asigne viticultures a bodegas hija con
 * control granular de acceso al cuaderno de campo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viticultor_assignments', function (Blueprint $table) {
            $table->id();

            // Viticultor asignado
            $table->foreignId('viticultor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Organización que recibe al viticultor (bodega o DO)
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            // Organización que realizó la asignación (DO padre o la misma bodega)
            $table->foreignId('assigned_by_org_id')
                ->nullable()
                ->constrained('organizations')
                ->nullOnDelete();

            // Usuario que ejecutó la asignación (traza de auditoría)
            $table->foreignId('assigned_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Acceso al cuaderno de campo (consentimiento GDPR separado de la activación)
            $table->boolean('cuaderno_access')->default(false);
            $table->timestamp('cuaderno_granted_at')->nullable();
            $table->timestamp('cuaderno_revoked_at')->nullable();

            $table->timestamps();

            // Un viticultor sólo puede estar asignado una vez por organización
            $table->unique(['viticultor_id', 'organization_id'], 'viticultor_org_unique');

            $table->index('organization_id');
            $table->index('assigned_by_org_id');
            $table->index('viticultor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viticultor_assignments');
    }
};
