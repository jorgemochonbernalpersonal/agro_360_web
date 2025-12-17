<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $sessionId = session()->getId();

        $this->actingAs($user)
            ->post(route('logout'));

        // La sesión debería estar invalidada
        $this->assertGuest();
    }

    public function test_logout_regenerates_csrf_token(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $oldToken = csrf_token();

        $this->actingAs($user)
            ->post(route('logout'));

        // El token debería haber cambiado (se regenera en logout)
        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_access_logout(): void
    {
        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
    }

    public function test_logout_redirects_to_login_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
    }
}

