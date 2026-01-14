<?php

namespace Tests\Unit\Models;

use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserBetaAccessTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_grants_beta_access_with_fixed_end_date()
    {
        $user = User::factory()->create();

        $user->grantBetaAccess();

        $this->assertTrue($user->is_beta_user);
        $this->assertTrue($user->beta_access_granted);
        $this->assertEquals('2026-06-30 23:59:59', $user->beta_ends_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function beta_user_has_access_before_expiration()
    {
        $user = User::factory()->create();
        $user->grantBetaAccess();

        // Simular que estamos antes del 30/06/2026
        Carbon::setTestNow('2026-01-01 12:00:00');

        $this->assertTrue($user->isBetaUser());
        $this->assertFalse($user->betaExpired());
        $this->assertTrue($user->hasActiveAccess());

        Carbon::setTestNow();  // Reset
    }

    #[Test]
    public function beta_user_is_blocked_after_expiration()
    {
        $user = User::factory()->create();
        $user->grantBetaAccess();

        // Simular que estamos después del 30/06/2026
        Carbon::setTestNow('2026-07-01 00:00:01');

        $this->assertTrue($user->isBetaUser());
        $this->assertTrue($user->betaExpired());
        $this->assertFalse($user->hasActiveAccess());

        Carbon::setTestNow();  // Reset
    }

    #[Test]
    public function beta_days_remaining_is_calculated_correctly()
    {
        $user = User::factory()->create();
        $user->grantBetaAccess();

        // Simular que estamos 30 días antes del fin
        Carbon::setTestNow('2026-05-31 12:00:00');

        $daysRemaining = $user->betaDaysRemaining();

        $this->assertGreaterThan(29, $daysRemaining);
        $this->assertLessThan(31, $daysRemaining);

        Carbon::setTestNow();  // Reset
    }

    #[Test]
    public function user_with_active_subscription_has_access_even_after_beta_expires()
    {
        $user = User::factory()->create();
        $user->grantBetaAccess();

        // Simular que beta expiró
        Carbon::setTestNow('2026-07-01 00:00:01');

        // Crear suscripción activa que expira DESPUÉS de la fecha mockeada
        Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),  // 2026-08-01
        ]);

        // Refrescar modelo para recargar relaciones
        $user = $user->fresh();

        // Lo importante: usuario tiene acceso a pesar de beta expirada
        $this->assertTrue($user->hasActiveSubscription());
        $this->assertTrue($user->hasActiveAccess());  // ✅ Acceso por suscripción

        Carbon::setTestNow();  // Reset
    }
}
