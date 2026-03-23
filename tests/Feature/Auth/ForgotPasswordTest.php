<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\ForgotPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cubre el componente ForgotPassword:
 * solicitud de enlace de restablecimiento de contraseña.
 */
class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    // ── Renderizado ───────────────────────────────────────────────────────────

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))
            ->assertOk();
    }

    public function test_livewire_component_renders(): void
    {
        Livewire::test(ForgotPassword::class)
            ->assertOk();
    }

    // ── sendResetLink ─────────────────────────────────────────────────────────

    public function test_sends_reset_link_for_existing_email(): void
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::RESET_LINK_SENT);

        $user = User::factory()->create(['email' => 'juan@example.com']);

        Livewire::test(ForgotPassword::class)
            ->set('email', 'juan@example.com')
            ->call('sendResetLink')
            ->assertHasNoErrors();
    }

    public function test_clears_email_field_after_send(): void
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::RESET_LINK_SENT);

        User::factory()->create(['email' => 'juan@example.com']);

        Livewire::test(ForgotPassword::class)
            ->set('email', 'juan@example.com')
            ->call('sendResetLink')
            ->assertSet('email', '');
    }

    public function test_shows_success_toast_when_link_sent(): void
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::RESET_LINK_SENT);

        User::factory()->create(['email' => 'juan@example.com']);

        Livewire::test(ForgotPassword::class)
            ->set('email', 'juan@example.com')
            ->call('sendResetLink')
            ->assertDispatched('toast');
    }

    public function test_shows_generic_message_for_nonexistent_email(): void
    {
        // Por seguridad, el sistema siempre devuelve mensaje genérico aunque no exista el email
        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::INVALID_USER);

        Livewire::test(ForgotPassword::class)
            ->set('email', 'noexiste@example.com')
            ->call('sendResetLink')
            ->assertHasNoErrors()
            ->assertDispatched('toast');
    }

    // ── Validaciones ──────────────────────────────────────────────────────────

    public function test_requires_email(): void
    {
        Livewire::test(ForgotPassword::class)
            ->set('email', '')
            ->call('sendResetLink')
            ->assertHasErrors(['email']);
    }

    public function test_requires_valid_email_format(): void
    {
        Livewire::test(ForgotPassword::class)
            ->set('email', 'no-es-email')
            ->call('sendResetLink')
            ->assertHasErrors(['email']);
    }
}
