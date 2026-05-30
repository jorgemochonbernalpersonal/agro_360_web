<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Enforcement de can_login en el lado WEB (no solo API).
 *
 * Antes, CheckCanLogin solo se aplicaba a rutas API y devolvía JSON, por lo que
 * una bodega/usuario desactivado conservaba su sesión web hasta cerrarla. Ahora el
 * middleware está en el grupo web y, para peticiones web, cierra sesión y redirige
 * al login. La impersonación de un admin queda exenta.
 */
class CheckCanLoginWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_user_is_redirected_to_login_on_web(): void
    {
        $user = User::factory()->create([
            'role'              => 'viticulturist',
            'can_login'         => false,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('viticulturist.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_enabled_user_is_not_redirected_to_login_on_web(): void
    {
        $user = User::factory()->create([
            'role'              => 'viticulturist',
            'can_login'         => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('viticulturist.dashboard'));

        // El gate de can_login no debe haber disparado la redirección al login.
        $this->assertNotSame(route('login'), $response->headers->get('Location'));
    }

    public function test_impersonating_admin_can_access_disabled_account_on_web(): void
    {
        $winery = User::factory()->create([
            'role'              => 'winery',
            'can_login'         => false,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($winery)
            ->withSession(['impersonating' => true, 'admin_id' => 1])
            ->get(route('winery.dashboard'));

        // La impersonación está exenta: NO se redirige al login por can_login.
        $this->assertNotSame(route('login'), $response->headers->get('Location'));
    }
}
