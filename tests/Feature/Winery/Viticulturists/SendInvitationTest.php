<?php

namespace Tests\Feature\Winery\Viticulturists;

use App\Livewire\Winery\Viticulturists\Show;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\ViticulturistInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

/**
 * Cubre la lógica de sendInvitation y revokeInvitation en Show.php:
 * rate limiting, unicidad de email, comportamiento con ghost vs. real email.
 *
 * InviteTest.php cubre la búsqueda y vinculación (Invite::class).
 * Este archivo cubre el envío de invitación desde el perfil del viticultor (Show::class).
 */
class SendInvitationTest extends WineryTestCase
{
    protected User $winery;
    protected User $viticulturist;
    protected WineryViticulturist $relation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = $this->makeWinery();

        $this->viticulturist = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => false,
            'email'     => 'viticultores.' . uniqid() . '@placeholder.agro365.es',
        ]);

        $this->relation = WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        $this->actingAs($this->winery);
    }

    // ── Rate limiting ─────────────────────────────────────────────────────────

    public function test_cannot_send_invitation_within_one_hour_of_previous_send(): void
    {
        // Simular envío hace 30 minutos (dentro de la ventana de 1 hora)
        $this->viticulturist->update(['invitation_sent_at' => now()->subMinutes(30)]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->set('inviteEmail', 'nuevo@example.com')
            ->call('sendInvitation')
            ->assertDispatched('toast');

        // El email NO debe haberse actualizado porque se bloqueó por rate limit
        $this->assertDatabaseMissing('users', [
            'id'    => $this->viticulturist->id,
            'email' => 'nuevo@example.com',
        ]);
    }

    public function test_can_send_invitation_after_one_hour_cooldown(): void
    {
        Notification::fake();

        // Envío hace 2 horas → fuera de la ventana de 1 hora
        $this->viticulturist->update(['invitation_sent_at' => now()->subHours(2)]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->set('inviteEmail', 'nuevo@example.com')
            ->call('sendInvitation')
            ->assertHasNoErrors();

        Notification::assertSentTo($this->viticulturist, ViticulturistInvitationNotification::class);
    }

    // ── Email ya registrado ───────────────────────────────────────────────────

    public function test_send_invitation_blocked_when_email_taken_by_another_user(): void
    {
        // Otro usuario ya tiene ese email
        User::factory()->create(['email' => 'tomado@example.com']);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->set('inviteEmail', 'tomado@example.com')
            ->call('sendInvitation')
            ->assertHasErrors(['inviteEmail']);
    }

    public function test_send_invitation_allowed_when_email_belongs_to_same_viticulturist(): void
    {
        Notification::fake();

        // El email ya pertenece al mismo viticulturist → debe permitirse
        $vitic = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => true,
            'email'     => 'propio@example.com',
        ]);

        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $vitic->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        Livewire::test(Show::class, ['viticulturist' => $vitic])
            ->set('inviteEmail', 'propio@example.com')
            ->call('sendInvitation')
            ->assertHasNoErrors();

        Notification::assertSentTo($vitic, ViticulturistInvitationNotification::class);
    }

    // ── Ghost viticulturist (placeholder email) ───────────────────────────────

    public function test_send_invitation_updates_email_for_ghost_viticulturist(): void
    {
        Notification::fake();

        // Ghost: email empieza con 'viticultores.' → se debe actualizar al enviar
        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->set('inviteEmail', 'real@example.com')
            ->call('sendInvitation')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id'    => $this->viticulturist->id,
            'email' => 'real@example.com',
        ]);
    }

    public function test_send_invitation_does_not_update_email_for_real_email_viticulturist(): void
    {
        Notification::fake();

        // Viticulturist con email real registrado → email no debe cambiar
        $vitic = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => true,
            'email'     => 'original@example.com',
        ]);

        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $vitic->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        Livewire::test(Show::class, ['viticulturist' => $vitic])
            ->set('inviteEmail', 'original@example.com')
            ->call('sendInvitation')
            ->assertHasNoErrors();

        // Email must remain unchanged
        $this->assertDatabaseHas('users', [
            'id'    => $vitic->id,
            'email' => 'original@example.com',
        ]);
    }

    // ── Token y expiración ────────────────────────────────────────────────────

    public function test_send_invitation_sets_token_and_expiry(): void
    {
        Notification::fake();

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->set('inviteEmail', 'nuevo@example.com')
            ->call('sendInvitation')
            ->assertHasNoErrors();

        $this->viticulturist->refresh();

        $this->assertNotNull($this->viticulturist->invitation_token);
        $this->assertNotNull($this->viticulturist->invitation_sent_at);
        $this->assertNotNull($this->viticulturist->invitation_expires_at);
        $this->assertTrue($this->viticulturist->invitation_expires_at->isAfter(now()));
    }

    public function test_send_invitation_sends_notification_with_token(): void
    {
        Notification::fake();

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->set('inviteEmail', 'nuevo@example.com')
            ->call('sendInvitation');

        Notification::assertSentTo(
            $this->viticulturist,
            ViticulturistInvitationNotification::class,
        );
    }

    // ── revokeInvitation ─────────────────────────────────────────────────────

    public function test_revoke_invitation_clears_token_fields(): void
    {
        $this->viticulturist->update([
            'invitation_token'      => 'some-token-abc123',
            'invitation_sent_at'    => now()->subHour(),
            'invitation_expires_at' => now()->addDays(6),
        ]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('revokeInvitation')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id'                    => $this->viticulturist->id,
            'invitation_token'      => null,
            'invitation_sent_at'    => null,
            'invitation_expires_at' => null,
        ]);
    }

    public function test_revoke_invitation_does_not_change_email(): void
    {
        $originalEmail = $this->viticulturist->email;

        $this->viticulturist->update([
            'invitation_token'      => 'token123',
            'invitation_sent_at'    => now()->subHour(),
            'invitation_expires_at' => now()->addDays(6),
        ]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('revokeInvitation');

        $this->assertDatabaseHas('users', [
            'id'    => $this->viticulturist->id,
            'email' => $originalEmail,
        ]);
    }
}
