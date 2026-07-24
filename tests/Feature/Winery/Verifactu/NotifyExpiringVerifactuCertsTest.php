<?php

namespace Tests\Feature\Winery\Verifactu;

use App\Models\User;
use App\Notifications\VerifactuCertificateExpiringNotification;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\WineryTestCase;

/**
 * Comando verifactu:notify-expiring-certs — avisa en umbrales de 30/7/1 días
 * antes de que caduque el certificado de firma de CADA usuario.
 */
class NotifyExpiringVerifactuCertsTest extends WineryTestCase
{
    public function test_notifies_user_whose_cert_expires_in_30_days(): void
    {
        Notification::fake();

        $winery = $this->makeWineryWithCertExpiry(['sif_cert_expires_at' => now()->addDays(30)]);

        $this->artisan('verifactu:notify-expiring-certs')->assertSuccessful();

        Notification::assertSentTo($winery, VerifactuCertificateExpiringNotification::class);
    }

    public function test_does_not_notify_user_whose_cert_expires_outside_thresholds(): void
    {
        Notification::fake();

        $winery = $this->makeWineryWithCertExpiry(['sif_cert_expires_at' => now()->addDays(15)]);

        $this->artisan('verifactu:notify-expiring-certs')->assertSuccessful();

        Notification::assertNotSentTo($winery, VerifactuCertificateExpiringNotification::class);
    }

    public function test_does_not_notify_user_without_certificate(): void
    {
        Notification::fake();

        $winery = $this->makeWineryWithCertExpiry(['sif_cert_expires_at' => null]);

        $this->artisan('verifactu:notify-expiring-certs')->assertSuccessful();

        Notification::assertNotSentTo($winery, VerifactuCertificateExpiringNotification::class);
    }

    public function test_notification_url_matches_the_users_role_settings_page(): void
    {
        Notification::fake();

        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
            'sif_cert_expires_at' => now()->addDay(),
        ]);

        $this->artisan('verifactu:notify-expiring-certs')->assertSuccessful();

        Notification::assertSentTo(
            $viticulturist,
            VerifactuCertificateExpiringNotification::class,
            fn ($notification) => str_contains($notification->url, 'viticulturist')
        );
    }

    private function makeWineryWithCertExpiry(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'winery',
            'email_verified_at' => now(),
        ], $attrs));
    }
}
