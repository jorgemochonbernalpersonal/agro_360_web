<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Certificado digital VeriFactu por emisor: cada usuario (bodega/productor/
 * viticultor) es su propio obligado tributario y firma con su propio
 * certificado, en vez de un certificado único de plataforma.
 *
 * - sif_cert_path: ruta en disco privado del .p12/.pfx (cifrado en reposo).
 * - sif_cert_password: contraseña del PKCS#12, cifrada (cast 'encrypted').
 * - sif_cert_uploaded_at / sif_cert_expires_at: metadatos para avisar de caducidad.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sif_cert_path')->nullable()->after('dni');
            $table->text('sif_cert_password')->nullable()->after('sif_cert_path');
            $table->timestamp('sif_cert_uploaded_at')->nullable()->after('sif_cert_password');
            $table->timestamp('sif_cert_expires_at')->nullable()->after('sif_cert_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'sif_cert_path',
                'sif_cert_password',
                'sif_cert_uploaded_at',
                'sif_cert_expires_at',
            ]);
        });
    }
};
