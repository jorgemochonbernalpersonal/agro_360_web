<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cubre el componente ResetPassword:
 * restablecimiento de contraseña mediante enlace de email.
 */
class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    // ── Renderizado ───────────────────────────────────────────────────────────

    public function test_reset_password_page_renders(): void
    {
        $this->get(route('password.reset', ['token' => 'cualquier-token']))
            ->assertOk();
    }

    public function test_livewire_component_renders(): void
    {
        Livewire::test(ResetPassword::class, ['token' => 'any-token'])
            ->assertOk();
    }

    // ── mount: validación de token y email ────────────────────────────────────

    public function test_valid_token_and_email_marks_token_valid(): void
    {
        $user  = User::factory()->create(['email' => 'juan@example.com']);
        $token = Password::createToken($user);

        Livewire::test(ResetPassword::class, [
            'token' => $token,
            'email' => 'juan@example.com',
        ])->assertSet('tokenValid', true);
    }

    public function test_nonexistent_email_redirects_to_request(): void
    {
        Livewire::test(ResetPassword::class, [
            'token' => 'any-token',
            'email' => 'noexiste@example.com',
        ])->assertRedirect(route('password.request'));
    }

    public function test_missing_reset_record_marks_token_invalid(): void
    {
        // Usuario existe pero no hay registro en password_reset_tokens
        $user = User::factory()->create(['email' => 'juan@example.com']);

        Livewire::test(ResetPassword::class, [
            'token' => 'token-sin-registro',
            'email' => 'juan@example.com',
        ])->assertSet('tokenValid', false);
    }

    // ── resetPassword: flujo correcto ─────────────────────────────────────────

    public function test_can_reset_password_with_valid_token(): void
    {
        $user  = User::factory()->create(['email' => 'juan@example.com']);
        $token = Password::createToken($user);

        Livewire::test(ResetPassword::class, [
            'token' => $token,
            'email' => 'juan@example.com',
        ])
            ->set('password', 'NuevaClave1!')
            ->set('password_confirmation', 'NuevaClave1!')
            ->call('resetPassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NuevaClave1!', $user->fresh()->password));
    }

    public function test_redirects_to_login_after_reset(): void
    {
        $user  = User::factory()->create(['email' => 'juan@example.com']);
        $token = Password::createToken($user);

        Livewire::test(ResetPassword::class, [
            'token' => $token,
            'email' => 'juan@example.com',
        ])
            ->set('password', 'NuevaClave1!')
            ->set('password_confirmation', 'NuevaClave1!')
            ->call('resetPassword')
            ->assertRedirect(route('login'));
    }

    // ── resetPassword: validaciones ───────────────────────────────────────────

    public function test_requires_email(): void
    {
        Livewire::test(ResetPassword::class, ['token' => 'any'])
            ->set('email', '')
            ->set('password', 'NuevaClave1!')
            ->set('password_confirmation', 'NuevaClave1!')
            ->call('resetPassword')
            ->assertHasErrors(['email']);
    }

    public function test_requires_password(): void
    {
        $user  = User::factory()->create(['email' => 'juan@example.com']);
        $token = Password::createToken($user);

        Livewire::test(ResetPassword::class, [
            'token' => $token,
            'email' => 'juan@example.com',
        ])
            ->set('password', '')
            ->set('password_confirmation', '')
            ->call('resetPassword')
            ->assertHasErrors(['password']);
    }

    public function test_rejects_password_mismatch(): void
    {
        $user  = User::factory()->create(['email' => 'juan@example.com']);
        $token = Password::createToken($user);

        Livewire::test(ResetPassword::class, [
            'token' => $token,
            'email' => 'juan@example.com',
        ])
            ->set('password', 'NuevaClave1!')
            ->set('password_confirmation', 'OtraClave1!')
            ->call('resetPassword')
            ->assertHasErrors(['password']);
    }

    public function test_invalid_token_throws_validation_error(): void
    {
        $user = User::factory()->create(['email' => 'juan@example.com']);
        // Crear un registro en password_reset_tokens para que pase mount
        Password::createToken($user);

        // Pero usar un token diferente en resetPassword
        Livewire::test(ResetPassword::class, [
            'token' => 'token-incorrecto',
            'email' => 'juan@example.com',
        ])
            ->set('password', 'NuevaClave1!')
            ->set('password_confirmation', 'NuevaClave1!')
            ->call('resetPassword')
            ->assertHasErrors(['email']);
    }
}
