<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\CheckCanLogin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CheckCanLoginMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    // ── Tests directos sobre el middleware (sin pasar por el auth guard) ──────

    private function runMiddleware(User $user): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create('/api/v1/test');
        $request->setUserResolver(fn () => $user);

        return (new CheckCanLogin())->handle(
            $request,
            fn () => response()->json(['ok' => true])
        );
    }

    public function test_middleware_blocks_disabled_user_directly(): void
    {
        $user = User::factory()->create(['can_login' => false]);

        $response = $this->runMiddleware($user);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals(
            'Cuenta desactivada. Contacta con soporte.',
            json_decode($response->getContent(), true)['message']
        );
    }

    public function test_middleware_passes_enabled_user_directly(): void
    {
        $user = User::factory()->create(['can_login' => true]);

        $response = $this->runMiddleware($user);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_revokes_tokens_when_blocking(): void
    {
        $user = User::factory()->create(['can_login' => false]);
        $user->createToken('mobile');
        $user->createToken('tablet');

        $this->runMiddleware($user);

        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_middleware_passes_when_user_is_null(): void
    {
        $request = Request::create('/api/v1/test');
        $request->setUserResolver(fn () => null);

        $response = (new CheckCanLogin())->handle(
            $request,
            fn () => response()->json(['ok' => true])
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_disabled_user_gets_403_on_protected_routes(): void
    {
        $user  = User::factory()->create(['can_login' => false]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Cuenta desactivada. Contacta con soporte.');
    }

    public function test_disabled_user_tokens_are_revoked_on_access(): void
    {
        $user = User::factory()->create(['can_login' => false]);
        $user->createToken('mobile');
        $user->createToken('tablet');
        $token = $user->createToken('watch')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me');

        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_enabled_user_can_access_protected_routes(): void
    {
        $user  = User::factory()->create(['can_login' => true]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertStatus(200);
    }

    public function test_user_disabled_after_login_has_tokens_revoked_on_next_request(): void
    {
        $user  = User::factory()->create(['can_login' => true]);
        $token = $user->createToken('mobile')->plainTextToken;
        $user->createToken('tablet');

        // Primero accede con éxito
        $this->withToken($token)->getJson('/api/v1/me')->assertStatus(200);
        $this->assertEquals(2, $user->tokens()->count());

        // Administrador desactiva al usuario
        $user->update(['can_login' => false]);

        // Resetear la caché del auth guard para que el siguiente request cargue el user fresco de DB
        $this->app['auth']->forgetGuards();

        // El middleware revoca todos los tokens al detectar can_login=false
        $this->withToken($token)->getJson('/api/v1/me');

        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_unauthenticated_request_returns_401_not_403(): void
    {
        $this->getJson('/api/v1/me')->assertStatus(401);
    }
}
