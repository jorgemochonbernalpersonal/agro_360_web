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
        $expected = now()->addMonths(3)->endOfDay()->format('Y-m-d H:i:s');
        $this->assertEquals($expected, $user->beta_ends_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function founder_gets_12_months_beta_instead_of_3()
    {
        $user = User::factory()->create(['is_founder' => true]);

        $user->grantBetaAccess();

        $this->assertTrue($user->is_beta_user);
        $expected = now()->addMonths(12)->endOfDay()->format('Y-m-d H:i:s');
        $this->assertEquals($expected, $user->beta_ends_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function non_founder_gets_3_months_beta()
    {
        $user = User::factory()->create(['is_founder' => false]);

        $user->grantBetaAccess();

        $this->assertTrue($user->is_beta_user);
        $expected = now()->addMonths(3)->endOfDay()->format('Y-m-d H:i:s');
        $this->assertEquals($expected, $user->beta_ends_at->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function founder_has_active_access_for_a_year()
    {
        Carbon::setTestNow('2026-01-01');

        $user = User::factory()->create(['is_founder' => true]);
        $user->grantBetaAccess();

        // A los 11 meses sigue activo
        Carbon::setTestNow('2026-12-01');
        $this->assertTrue($user->fresh()->hasActiveAccess());

        // A los 12 meses + 1 día ha expirado
        Carbon::setTestNow('2027-01-02');
        $this->assertFalse($user->fresh()->hasActiveAccess());

        Carbon::setTestNow();
    }

    #[Test]
    public function grant_beta_cascades_to_linked_viticulturists()
    {
        $winery = User::factory()->create(['role' => 'winery', 'is_beta_user' => false]);
        $vit    = User::factory()->create(['role' => 'viticulturist', 'is_beta_user' => false]);

        \Illuminate\Support\Facades\DB::table('winery_viticulturist')->insert([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'source'           => 'own',
            'assigned_by'      => $winery->id,
        ]);

        $winery->grantBetaAccess();

        $this->assertTrue($vit->fresh()->is_beta_user);
        $this->assertEquals(
            $winery->fresh()->beta_ends_at->format('Y-m-d'),
            $vit->fresh()->beta_ends_at->format('Y-m-d')
        );
    }

    #[Test]
    public function grant_beta_cascade_does_not_overwrite_existing_beta()
    {
        $winery = User::factory()->create(['role' => 'winery']);
        $vit    = User::factory()->create([
            'role'         => 'viticulturist',
            'is_beta_user' => true,               // ya tenía beta
            'beta_ends_at' => now()->addMonths(6),
        ]);

        \Illuminate\Support\Facades\DB::table('winery_viticulturist')->insert([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $vit->id,
            'source'           => 'own',
            'assigned_by'      => $winery->id,
        ]);

        $winery->grantBetaAccess(); // solo afecta a quienes tienen is_beta_user=false

        // El viticultor sigue con su beta original (no se sobreescribe)
        $this->assertTrue($vit->fresh()->is_beta_user);
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
        // Fijar fecha base para que beta expire el 2026-06-01 + 3 meses = 2026-09-01
        Carbon::setTestNow('2026-06-01 12:00:00');

        $user = User::factory()->create(['role' => 'winery']); // winery no tiene free access
        $user->grantBetaAccess();

        // Avanzar después de la expiración (3 meses + 1 día)
        Carbon::setTestNow('2026-09-02 00:00:01');

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

        $endDate = $user->fresh()->beta_ends_at;

        // Simular que estamos 30 días antes del fin
        Carbon::setTestNow($endDate->copy()->subDays(30));

        $daysRemaining = $user->betaDaysRemaining();

        $this->assertGreaterThan(29, $daysRemaining);
        $this->assertLessThan(31, $daysRemaining);

        Carbon::setTestNow();  // Reset
    }

    #[Test]
    public function user_with_active_subscription_has_access_even_after_beta_expires()
    {
        Carbon::setTestNow('2026-03-01 12:00:00');

        $user = User::factory()->create(['role' => 'winery']);
        $user->grantBetaAccess();

        // Avanzar después de la expiración (3 meses + 1 día)
        Carbon::setTestNow('2026-06-02 00:00:01');

        // Crear suscripción activa que expira DESPUÉS de la fecha mockeada
        Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY_PRODUCER,
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
