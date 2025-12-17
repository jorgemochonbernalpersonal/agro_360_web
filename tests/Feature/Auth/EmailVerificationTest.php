<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Livewire\Livewire;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => null, // No verificado
            'role' => 'viticulturist',
        ]);

        $this->get('/login');

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_verified_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(), // Verificado
            'role' => 'viticulturist',
        ]);

        $this->get('/login');

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('viticulturist.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('viticulturist.dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verification_notice_page_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertSee('Verifica tu Email');
    }

    public function test_user_can_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_can_verify_email_via_link(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertRedirect(route('verification.verified'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verified_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'viticulturist',
        ]);

        $this->actingAs($user)
            ->get(route('viticulturist.dashboard'))
            ->assertStatus(200);
    }

    public function test_verification_check_endpoint_returns_false_for_unverified_user(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->get(route('verification.check'));

        $response->assertStatus(200);
        $response->assertJson([
            'verified' => false,
        ]);
    }

    public function test_verification_check_endpoint_returns_true_for_verified_user(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'viticulturist',
        ]);

        $response = $this->actingAs($user)
            ->get(route('verification.check'));

        $response->assertStatus(200);
        $response->assertJson([
            'verified' => true,
        ]);
        $response->assertJsonStructure([
            'verified',
            'redirect_url',
        ]);
    }

    public function test_verification_send_is_rate_limited(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Intentar enviar más de 6 veces en 1 minuto
        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($user)
                ->post(route('verification.send'));
        }

        // El séptimo intento debería ser bloqueado
        $response = $this->actingAs($user)
            ->post(route('verification.send'));

        $response->assertStatus(429); // Too Many Requests
    }

    public function test_public_registration_sends_verification_email(): void
    {
        Notification::fake();

        $this->get('/register');

        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'viticulturist')
            ->call('register');

        $user = User::where('email', 'test@example.com')->first();
        
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertNull($user->email_verified_at);
    }
}

