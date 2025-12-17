<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_with_correct_role_can_access_route(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_user_with_incorrect_role_gets_403(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($viticulturist)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
        $response->assertSee('No tienes permiso');
    }

    public function test_supervisor_can_access_supervisor_routes(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($supervisor)
            ->get(route('supervisor.dashboard'));

        $response->assertStatus(200);
    }

    public function test_supervisor_cannot_access_admin_routes(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($supervisor)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_winery_can_access_winery_routes(): void
    {
        $winery = User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($winery)
            ->get(route('winery.dashboard'));

        $response->assertStatus(200);
    }

    public function test_winery_cannot_access_supervisor_routes(): void
    {
        $winery = User::factory()->create([
            'role' => 'winery',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($winery)
            ->get(route('supervisor.dashboard'));

        $response->assertStatus(403);
    }

    public function test_viticulturist_can_access_viticulturist_routes(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($viticulturist)
            ->get(route('viticulturist.dashboard'));

        $response->assertStatus(200);
    }

    public function test_viticulturist_cannot_access_winery_routes(): void
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($viticulturist)
            ->get(route('winery.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_other_role_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Admin solo puede acceder a sus propias rutas
        // Las rutas están protegidas por role específico
        $otherRoutes = [
            'supervisor.dashboard',
            'winery.dashboard',
            'viticulturist.dashboard',
        ];

        foreach ($otherRoutes as $route) {
            $response = $this->actingAs($admin)
                ->get(route($route));

            $response->assertStatus(403);
        }
    }
}

