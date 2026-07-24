<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/**
 * Gestión del certificado digital VeriFactu de CADA usuario (bodega, productor
 * o viticultor): cada uno es su propio obligado tributario ante la AEAT y firma
 * sus facturas con su propio certificado — no con uno único de la plataforma.
 *
 * El .p12/.pfx se guarda CIFRADO en reposo (Crypt) en el disco privado ('local',
 * fuera de webroot). La contraseña se guarda cifrada en users.sif_cert_password
 * (cast 'encrypted').
 */
class VerifactuCertificateService
{
    private const DISK = 'local';

    /**
     * Valida y almacena el certificado del usuario. Rechaza el archivo si la
     * contraseña no lo abre (se detecta al subirlo, no al emitir la primera
     * factura).
     */
    public function store(User $user, UploadedFile $file, string $password): void
    {
        $raw = file_get_contents($file->getRealPath());

        $certs = [];
        if ($raw === false || ! openssl_pkcs12_read($raw, $certs, $password)) {
            throw new \RuntimeException(__('No se pudo abrir el certificado. Verifica la contraseña.'));
        }

        // Que la contraseña abra el .p12 no garantiza que sea el certificado del
        // emisor CORRECTO: subir el de otra bodega/persona se aceptaría y solo
        // reventaría ante la AEAT con un error críptico. Se comprueba al subir.
        $this->assertCertificateMatchesIssuer($certs['cert'] ?? null, $user);

        $path = $this->pathFor($user);
        Storage::disk(self::DISK)->put($path, Crypt::encryptString($raw));

        $user->forceFill([
            'sif_cert_path' => $path,
            'sif_cert_password' => $password,
            'sif_cert_uploaded_at' => now(),
            'sif_cert_expires_at' => $this->expiryFrom($certs['cert'] ?? null),
        ])->save();
    }

    /**
     * Elimina el certificado del usuario.
     */
    public function delete(User $user): void
    {
        if ($user->sif_cert_path && Storage::disk(self::DISK)->exists($user->sif_cert_path)) {
            Storage::disk(self::DISK)->delete($user->sif_cert_path);
        }

        $user->forceFill([
            'sif_cert_path' => null,
            'sif_cert_password' => null,
            'sif_cert_uploaded_at' => null,
            'sif_cert_expires_at' => null,
        ])->save();
    }

    public function hasCertificate(User $user): bool
    {
        return ! empty($user->sif_cert_path)
            && Storage::disk(self::DISK)->exists($user->sif_cert_path);
    }

    private function pathFor(User $user): string
    {
        return "certificates/verifactu/{$user->id}.p12";
    }

    /**
     * Rechaza un certificado cuyo titular no sea el NIF/DNI del usuario. TOLERANTE
     * a propósito: solo bloquea cuando el certificado identifica CLARAMENTE a otro
     * obligado fiscal (un token con forma de NIF/CIF que no es el nuestro). Si el
     * `subject` no lleva ninguna identificación fiscal reconocible (p. ej. un
     * certificado de test), no bloquea — mejor no rechazar uno válido por un
     * formato de `subject` inesperado que arriesgar un falso positivo.
     */
    private function assertCertificateMatchesIssuer(?string $certPem, User $user): void
    {
        $expected = $this->normalizeTaxId((string) ($user->dni ?? ''));
        if ($expected === '' || empty($certPem)) {
            return; // sin NIF del usuario con qué comparar
        }

        $parsed = openssl_x509_parse($certPem);
        if (! is_array($parsed) || empty($parsed['subject'])) {
            return; // subject ilegible: tolerante
        }

        // Aplana TODO el subject a un texto normalizado y busca el NIF con
        // independencia del campo (serialNumber FNMT 'IDCES-…', CN…) y del
        // formato con que venga incrustado.
        $haystack = $this->normalizeTaxId($this->flattenSubject($parsed['subject']));

        if (str_contains($haystack, $expected)) {
            return; // el NIF del usuario aparece en el titular: correcto
        }

        // No aparece. Solo bloqueamos si el certificado SÍ identifica a algún
        // obligado fiscal (token con forma de NIF/CIF): así se rechaza el de otra
        // entidad, pero un certificado sin identificación fiscal pasa (tolerante).
        if (preg_match('/[A-Z]?[0-9]{7,8}[A-Z0-9]/', $haystack)) {
            throw new \RuntimeException(__('El certificado pertenece a un NIF distinto al configurado en tus Datos Fiscales.'));
        }
    }

    private function normalizeTaxId(string $value): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $value));
    }

    /** Concatena todos los valores del subject X.509 (recursivo) en un solo texto. */
    private function flattenSubject(array $subject): string
    {
        $parts = [];
        array_walk_recursive($subject, function ($value) use (&$parts) {
            $parts[] = (string) $value;
        });

        return implode(' ', $parts);
    }

    /**
     * Fecha de caducidad (validTo) del certificado X.509, para avisar al usuario.
     */
    private function expiryFrom(?string $certPem): ?string
    {
        if (empty($certPem)) {
            return null;
        }

        $parsed = openssl_x509_parse($certPem);
        if (! is_array($parsed) || empty($parsed['validTo_time_t'])) {
            return null;
        }

        return date('Y-m-d H:i:s', (int) $parsed['validTo_time_t']);
    }
}
