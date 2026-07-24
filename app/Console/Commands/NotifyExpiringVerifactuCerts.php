<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\VerifactuCertificateExpiringNotification;
use Illuminate\Console\Command;

/**
 * Aviso proactivo de caducidad del certificado VeriFactu, para no descubrir que
 * ha caducado cuando una factura falla al emitir. Se avisa en umbrales
 * concretos (30/7/1 días); como cada umbral es una ventana de UN día no hay
 * avisos repetidos.
 */
class NotifyExpiringVerifactuCerts extends Command
{
    protected $signature = 'verifactu:notify-expiring-certs';

    protected $description = 'Avisa a los usuarios cuyo certificado VeriFactu caduca en 30, 7 o 1 días.';

    private const THRESHOLDS = [30, 7, 1];

    public function handle(): int
    {
        $sent = 0;

        foreach (self::THRESHOLDS as $days) {
            $start = now()->addDays($days)->startOfDay();
            $end = now()->addDays($days)->endOfDay();

            $users = User::whereNotNull('sif_cert_expires_at')
                ->whereBetween('sif_cert_expires_at', [$start, $end])
                ->get();

            foreach ($users as $user) {
                $user->notify(new VerifactuCertificateExpiringNotification(
                    $days,
                    $this->settingsUrl($user),
                    $user->sif_cert_expires_at->format('d/m/Y'),
                ));
                $this->line("  → Usuario {$user->id} ({$user->role}): cert caduca en {$days}d");
                $sent++;
            }
        }

        $this->info("Avisos de caducidad de certificado enviados: {$sent}.");

        return self::SUCCESS;
    }

    private function settingsUrl(User $user): string
    {
        $routeName = match ($user->role) {
            User::ROLE_PRODUCER => 'producer.settings',
            User::ROLE_VITICULTURIST => 'viticulturist.settings',
            default => 'winery.settings',
        };

        return route($routeName, ['tab' => 'fiscal']);
    }
}
