<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\ApiRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ApiRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_blocks_wrong_role_directly(): void
    {
        $user = User::factory()->viticulturist()->create(['can_login' => true]);

        $response = $this->runMiddleware($user, 'winery', 'producer');

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_middleware_allows_correct_role_directly(): void
    {
        $user = User::factory()->winery()->create(['can_login' => true]);

        $response = $this->runMiddleware($user, 'winery', 'producer');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_allows_when_no_roles_required(): void
    {
        $user = User::factory()->viticulturist()->create(['can_login' => true]);

        $response = $this->runMiddleware($user); // sin roles → acceso libre

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_allows_producer_on_winery_routes_directly(): void
    {
        $user = User::factory()->producer()->create(['can_login' => true]);

        $response = $this->runMiddleware($user, 'winery', 'producer');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_blocks_producer_on_supervisor_routes_directly(): void
    {
        $user = User::factory()->producer()->create(['can_login' => true]);

        $response = $this->runMiddleware($user, 'supervisor');

        $this->assertEquals(403, $response->getStatusCode());
    }

    // ── Winery endpoints ──────────────────────────────────────────────────────

    public function test_winery_user_can_access_winery_endpoints(): void
    {
        $user = User::factory()->winery()->create(['can_login' => true]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/winery/containers');

        $this->assertNotEquals(403, $response->status());
    }

    public function test_viticulturist_cannot_access_winery_endpoints(): void
    {
        $user = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/winery/containers')
            ->assertStatus(403);
    }

    public function test_supervisor_cannot_access_winery_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'supervisor', 'can_login' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/winery/containers')
            ->assertStatus(403);
    }

    // ── Viticulturist endpoints ───────────────────────────────────────────────

    public function test_viticulturist_user_can_access_viticulturist_endpoints(): void
    {
        $user = User::factory()->viticulturist()->create(['can_login' => true]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/viticulturist/plots');

        $this->assertNotEquals(403, $response->status());
    }

    public function test_winery_cannot_access_viticulturist_endpoints(): void
    {
        $user = User::factory()->winery()->create(['can_login' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/viticulturist/plots')
            ->assertStatus(403);
    }

    public function test_supervisor_cannot_access_viticulturist_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'supervisor', 'can_login' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/viticulturist/plots')
            ->assertStatus(403);
    }

    // ── Producer (acceso dual) ────────────────────────────────────────────────

    public function test_producer_can_access_winery_endpoints(): void
    {
        $user = User::factory()->producer()->create(['can_login' => true]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/winery/containers');

        $this->assertNotEquals(403, $response->status());
    }

    public function test_producer_can_access_viticulturist_endpoints(): void
    {
        $user = User::factory()->producer()->create(['can_login' => true]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/viticulturist/plots');

        $this->assertNotEquals(403, $response->status());
    }

    // ── Supervisor endpoints ──────────────────────────────────────────────────

    public function test_supervisor_can_access_supervisor_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'supervisor', 'can_login' => true]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/supervisor/wineries');

        $this->assertNotEquals(403, $response->status());
    }

    public function test_winery_cannot_access_supervisor_endpoints(): void
    {
        $user = User::factory()->winery()->create(['can_login' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/supervisor/wineries')
            ->assertStatus(403);
    }

    public function test_viticulturist_cannot_access_supervisor_endpoints(): void
    {
        $user = User::factory()->viticulturist()->create(['can_login' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/supervisor/wineries')
            ->assertStatus(403);
    }

    // ── Rutas comunes no requieren rol ────────────────────────────────────────

    public function test_any_authenticated_user_can_access_me(): void
    {
        foreach (['winery', 'viticulturist', 'producer', 'supervisor'] as $role) {
            $user = User::factory()->create(['role' => $role, 'can_login' => true]);

            $this->actingAs($user, 'sanctum')
                ->getJson('/api/v1/me')
                ->assertStatus(200);
        }
    }

    // ── Tests directos sobre el middleware (sin controllers) ──────────────────
    // Evitan que las policies de los controllers enmascaren el comportamiento del middleware

    private function runMiddleware(User $user, string ...$roles): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create('/api/v1/test');
        $request->setUserResolver(fn () => $user);

        return (new ApiRole)->handle(
            $request,
            fn () => response()->json(['ok' => true]),
            ...$roles
        );
    }
}
