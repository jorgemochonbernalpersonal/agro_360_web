<?php

namespace App\Livewire\Concerns;

use App\Services\VerifactuCertificateService;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

/**
 * Subida/eliminación del certificado VeriFactu del usuario autenticado.
 *
 * Requiere:
 *   - WithToastNotifications (toastSuccess / toastError)
 *
 * Cada usuario (bodega/productor/viticultor) firma sus propias facturas con su
 * propio certificado — ver VerifactuCertificateService. No hay certificado
 * único de plataforma.
 */
trait WithVerifactuCertificate
{
    use WithFileUploads;

    public $verifactuCertificateFile = null;

    public string $verifactuCertificatePassword = '';

    public bool $hasVerifactuCertificate = false;

    public ?string $verifactuCertificateExpiresAt = null;

    public bool $verifactuCertificateExpiringSoon = false;

    public bool $verifactuCertificateExpired = false;

    public function loadVerifactuCertificate(): void
    {
        $user = Auth::user();

        $this->hasVerifactuCertificate = ! empty($user->sif_cert_path);
        $this->verifactuCertificateExpiresAt = $user->sif_cert_expires_at?->format('d/m/Y');
        $this->verifactuCertificateExpired = (bool) $user->sif_cert_expires_at?->isPast();
        $this->verifactuCertificateExpiringSoon = ! $this->verifactuCertificateExpired
            && $user->sif_cert_expires_at
            && now()->diffInDays($user->sif_cert_expires_at, false) <= 30;
    }

    public function uploadVerifactuCertificate(): void
    {
        $this->validate([
            'verifactuCertificateFile' => ['required', 'file', 'max:5120', 'extensions:p12,pfx'],
            'verifactuCertificatePassword' => ['required', 'string', 'max:255'],
        ], [
            'verifactuCertificateFile.required' => __('Selecciona un archivo de certificado (.p12 o .pfx).'),
            'verifactuCertificateFile.extensions' => __('El certificado debe ser un archivo .p12 o .pfx.'),
            'verifactuCertificateFile.max' => __('El certificado no puede superar los 5 MB.'),
            'verifactuCertificatePassword.required' => __('Introduce la contraseña del certificado.'),
        ]);

        try {
            app(VerifactuCertificateService::class)->store(
                Auth::user(),
                $this->verifactuCertificateFile,
                $this->verifactuCertificatePassword,
            );

            $this->reset(['verifactuCertificateFile', 'verifactuCertificatePassword']);
            $this->loadVerifactuCertificate();
            $this->toastSuccess(__('Certificado VeriFactu cargado correctamente.'));
        } catch (\RuntimeException $e) {
            $this->addError('verifactuCertificateFile', $e->getMessage());
        }
    }

    public function removeVerifactuCertificate(): void
    {
        app(VerifactuCertificateService::class)->delete(Auth::user());

        $this->loadVerifactuCertificate();
        $this->toastSuccess(__('Certificado VeriFactu eliminado.'));
    }
}
