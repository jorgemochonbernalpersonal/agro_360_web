<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class CheckBetaAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function middleware_blocks_beta_expired_users_without_subscription()
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VITICULTURIST,
        ]);
        $user->grantBetaAccess();
        
        // Simular beta expirada
        Carbon::setTestNow('2026-07-01 00:00:01');
        
        $response = $this->actingAs($user)->get(route('viticulturist.dashboard'));
        
        $response->assertRedirect(route('beta.expired'));
        
        Carbon::setTestNow(); // Reset
    }

    /** @test */
    public function middleware_allows_access_to_users_with_active_subscription()
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VITICULTURIST,
            'email_verified_at' => now(),
        ]);
        $user->grantBetaAccess();
        
        // Simular beta expirada
        Carbon::setTestNow('2026-07-01 00:00:01');
        
        // Crear suscripción activa que expira DESPUÉS de la fecha mockeada
        Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(), // 2026-08-01
        ]);
        
        // Obtener usuario fresco desde DB antes de actingAs
        $freshUser = User::find($user->id);
        
        $this->assertTrue($freshUser->hasActiveSubscription());
        $response = $this->actingAs($freshUser)->get(route('viticulturist.dashboard'));
        
        $response->assertOk(); // ✅ Acceso permitido
        
        Carbon::setTestNow(); // Reset
    }

    /** @test */
    public function beta_expired_page_is_accessible()
    {
        $user = User::factory()->create();
        $user->grantBetaAccess();
        
        Carbon::setTestNow('2026-07-01 00:00:01');
        
        $response = $this->actingAs($user)->get(route('beta.expired'));
        
        $response->assertOk();
        $response->assertSee('Tu período de beta ha finalizado');
        
        Carbon::setTestNow(); // Reset
    }

    /** @test */
    public function all_protected_routes_require_beta_access()
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VITICULTURIST,
            'email_verified_at' => now(),
        ]);
        $user->grantBetaAccess();
        
        // Simular beta expirada
        Carbon::setTestNow('2026-07-01 00:00:01');
        
        // Probar diferentes rutas protegidas por check.beta
        $routes = [
            route('viticulturist.dashboard'),
            route('plots.index'),
            route('sigpac.codes'),
        ];
        
        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);
            $this->assertEquals(302, $response->status(), "Route {$route} should redirect");
            $this->assertEquals(route('beta.expired'), $response->headers->get('Location'));
        }
        
        Carbon::setTestNow(); // Reset
    }
}

