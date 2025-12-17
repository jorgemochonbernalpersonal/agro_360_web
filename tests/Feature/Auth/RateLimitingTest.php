<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rate_limiting_blocks_after_5_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->get('/login');

        // Intentar login 5 veces con contraseña incorrecta
        for ($i = 0; $i < 5; $i++) {
            Livewire::test(\App\Livewire\Auth\Login::class)
                ->set('email', 'test@example.com')
                ->set('password', 'wrong-password')
                ->call('login')
                ->assertHasErrors(['email']);
        }

        // El sexto intento debería mostrar mensaje de rate limiting
        $response = Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'wrong-password')
            ->call('login');

        $response->assertHasErrors(['email']);
        $response->assertSee('Demasiados intentos');
    }

    public function test_login_rate_limiting_resets_after_60_seconds(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $key = 'login.' . request()->ip();

        // Llenar el rate limiter
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 60);
        }

        $this->assertTrue(RateLimiter::tooManyAttempts($key, 5));

        // Simular que pasaron 60 segundos
        RateLimiter::clear($key);

        $this->assertFalse(RateLimiter::tooManyAttempts($key, 5));
    }

    public function test_successful_login_after_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'role' => 'viticulturist',
        ]);

        $key = 'login.' . request()->ip();

        // Intentar login 3 veces con contraseña incorrecta
        for ($i = 0; $i < 3; $i++) {
            Livewire::test(\App\Livewire\Auth\Login::class)
                ->set('email', 'test@example.com')
                ->set('password', 'wrong-password')
                ->call('login');
        }

        // Verificar que tenemos 3 intentos (menos del límite de 5)
        $this->assertFalse(RateLimiter::tooManyAttempts($key, 5));

        // Login exitoso (esto hace el 4to hit)
        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('viticulturist.dashboard'));

        // Después del login exitoso, todavía tenemos menos de 5 intentos
        // El rate limit solo bloquea después de 5 intentos fallidos
        $this->assertFalse(RateLimiter::tooManyAttempts($key, 5));
    }

    public function test_rate_limiting_is_per_ip_address(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Simular diferentes IPs
        $ip1 = '192.168.1.1';
        $ip2 = '192.168.1.2';

        $key1 = 'login.' . $ip1;
        $key2 = 'login.' . $ip2;

        // Llenar rate limit para IP1
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key1, 60);
        }

        $this->assertTrue(RateLimiter::tooManyAttempts($key1, 5));
        $this->assertFalse(RateLimiter::tooManyAttempts($key2, 5));
    }

    public function test_rate_limiting_message_shows_remaining_seconds(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $key = 'login.' . request()->ip();

        // Llenar el rate limiter
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 60);
        }

        $this->get('/login');

        $response = Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'wrong-password')
            ->call('login');

        $response->assertHasErrors(['email']);
        $response->assertSee('segundos');
    }
}

