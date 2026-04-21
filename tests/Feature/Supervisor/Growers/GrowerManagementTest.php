<?php

namespace Tests\Feature\Supervisor\Growers;

use App\Livewire\Supervisor\Growers\Index;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class GrowerManagementTest extends SupervisorTestCase
{
    // ── createGrower ──────────────────────────────────────────────────────────

    public function test_supervisor_can_create_ghost_viticulturist(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('createName', 'Juan Viña')
            ->set('createEmail', 'juan@viña.es')
            ->call('createGrower')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('users', [
            'name'      => 'Juan Viña',
            'email'     => 'juan@viña.es',
            'role'      => 'viticulturist',
            'can_login' => false,
        ]);
    }

    public function test_created_grower_is_added_to_supervisor_pool(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('createName', 'Ana Campo')
            ->call('createGrower');

        $viticulturist = User::where('name', 'Ana Campo')->first();

        $this->assertDatabaseHas('supervisor_viticulturist', [
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
        ]);
    }

    public function test_create_without_email_generates_placeholder_email(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('createName', 'Sin Email')
            ->set('createEmail', '')
            ->call('createGrower');

        $user = User::where('name', 'Sin Email')->first();
        $this->assertStringStartsWith('viticultores.', $user->email);
    }

    public function test_create_with_dni_normalizes_to_uppercase(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('createName', 'Pedro DNI')
            ->set('createDni', '12345678a')
            ->call('createGrower');

        $this->assertDatabaseHas('users', ['dni' => '12345678A']);
    }

    public function test_create_grower_requires_name(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('createName', '')
            ->call('createGrower')
            ->assertHasErrors(['createName']);
    }

    public function test_create_grower_rejects_duplicate_email(): void
    {
        $supervisor = $this->makeSupervisor();
        User::factory()->create(['email' => 'taken@test.es']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('createName', 'Test')
            ->set('createEmail', 'taken@test.es')
            ->call('createGrower')
            ->assertHasErrors(['createEmail']);
    }

    public function test_create_grower_rejects_dni_already_used_by_active_user(): void
    {
        $supervisor = $this->makeSupervisor();
        User::factory()->create(['dni' => '99999999Z', 'can_login' => true]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('createName', 'Nuevo')
            ->set('createDni', '99999999Z')
            ->call('createGrower')
            ->assertHasErrors(['createDni']);
    }

    public function test_create_grower_rejects_dni_already_used_by_any_user(): void
    {
        $supervisor = $this->makeSupervisor();
        User::factory()->create(['dni' => '88888888Y', 'can_login' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('createName', 'Nuevo')
            ->set('createDni', '88888888Y')
            ->call('createGrower')
            ->assertHasErrors(['createDni']);
    }

    public function test_create_closes_modal_on_success(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('createName', 'Carlos Loma')
            ->call('createGrower')
            ->assertSet('showCreateModal', false);
    }

    public function test_created_grower_appears_in_list(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('createName', 'Rosa Viñedo')
            ->call('createGrower')
            ->assertSee('Rosa Viñedo');
    }

    // ── openInviteModal ───────────────────────────────────────────────────────

    public function test_open_invite_modal_sets_state_for_ghost_grower(): void
    {
        $supervisor    = $this->makeSupervisor();
        $ghost         = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update(['can_login' => false, 'email' => 'real@grower.es']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $ghost->id)
            ->assertSet('inviteGrowerId', $ghost->id)
            ->assertSet('showInviteModal', true)
            ->assertSet('inviteEmail', 'real@grower.es');
    }

    public function test_open_invite_modal_clears_email_for_placeholder_grower(): void
    {
        $supervisor = $this->makeSupervisor();
        $ghost      = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update(['can_login' => false, 'email' => 'viticultores.uuid@noemail.agro365.es']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $ghost->id)
            ->assertSet('inviteEmail', '');
    }

    public function test_open_invite_modal_rejects_active_grower(): void
    {
        $supervisor  = $this->makeSupervisor();
        $activeGrower = $this->makeViticulturistForSupervisor($supervisor);
        $activeGrower->update(['can_login' => true]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $activeGrower->id);
    }

    public function test_close_invite_modal_resets_state(): void
    {
        $supervisor = $this->makeSupervisor();
        $ghost      = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update(['can_login' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $ghost->id)
            ->call('closeInviteModal')
            ->assertSet('showInviteModal', false)
            ->assertSet('inviteGrowerId', null)
            ->assertSet('inviteEmail', '');
    }

    // ── sendInvitation ────────────────────────────────────────────────────────

    public function test_send_invitation_sets_token_and_timestamps(): void
    {
        Notification::fake();

        $supervisor = $this->makeSupervisor();
        $ghost      = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update(['can_login' => false, 'invitation_sent_at' => null]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $ghost->id)
            ->set('inviteEmail', 'invite@viña.es')
            ->call('sendInvitation')
            ->assertDispatched('toast');

        $fresh = $ghost->fresh();
        $this->assertNotNull($fresh->invitation_token);
        $this->assertNotNull($fresh->invitation_sent_at);
        $this->assertNotNull($fresh->invitation_expires_at);
    }

    public function test_send_invitation_updates_email_when_grower_had_placeholder(): void
    {
        Notification::fake();

        $supervisor = $this->makeSupervisor();
        $ghost      = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update(['can_login' => false, 'email' => 'viticultores.test@noemail.agro365.es']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $ghost->id)
            ->set('inviteEmail', 'nuevo@email.es')
            ->call('sendInvitation');

        $this->assertEquals('nuevo@email.es', $ghost->fresh()->email);
    }

    public function test_send_invitation_keeps_original_email_when_real(): void
    {
        Notification::fake();

        $supervisor = $this->makeSupervisor();
        $ghost      = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update(['can_login' => false, 'email' => 'original@viña.es']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $ghost->id)
            ->set('inviteEmail', 'original@viña.es')
            ->call('sendInvitation');

        $this->assertEquals('original@viña.es', $ghost->fresh()->email);
    }

    public function test_send_invitation_requires_email(): void
    {
        $supervisor = $this->makeSupervisor();
        $ghost      = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update(['can_login' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $ghost->id)
            ->set('inviteEmail', '')
            ->call('sendInvitation')
            ->assertHasErrors(['inviteEmail']);
    }

    public function test_send_invitation_rejects_email_taken_by_another_user(): void
    {
        $supervisor = $this->makeSupervisor();
        $ghost      = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update(['can_login' => false, 'email' => 'viticultores.x@noemail.agro365.es']);

        User::factory()->create(['email' => 'taken@other.es']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $ghost->id)
            ->set('inviteEmail', 'taken@other.es')
            ->call('sendInvitation')
            ->assertHasErrors(['inviteEmail']);
    }

    public function test_send_invitation_rate_limited_within_one_hour(): void
    {
        $supervisor = $this->makeSupervisor();
        $ghost      = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update([
            'can_login'          => false,
            'invitation_sent_at' => now()->subMinutes(30),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $ghost->id)
            ->set('inviteEmail', 'invite@viña.es')
            ->call('sendInvitation')
            ->assertDispatched('toast');

        // Token must NOT be regenerated (rate limited)
        $this->assertNull($ghost->fresh()->invitation_token);
    }

    public function test_send_invitation_allowed_after_one_hour(): void
    {
        Notification::fake();

        $supervisor = $this->makeSupervisor();
        $ghost      = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update([
            'can_login'          => false,
            'invitation_sent_at' => now()->subMinutes(61),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $ghost->id)
            ->set('inviteEmail', 'invite@viña.es')
            ->call('sendInvitation')
            ->assertDispatched('toast');

        $this->assertNotNull($ghost->fresh()->invitation_token);
    }

    public function test_send_invitation_closes_modal_on_success(): void
    {
        Notification::fake();

        $supervisor = $this->makeSupervisor();
        $ghost      = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update(['can_login' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openInviteModal', $ghost->id)
            ->set('inviteEmail', 'invite@viña.es')
            ->call('sendInvitation')
            ->assertSet('showInviteModal', false);
    }

    // ── revokeInvitation ──────────────────────────────────────────────────────

    public function test_supervisor_can_revoke_invitation(): void
    {
        $supervisor = $this->makeSupervisor();
        $ghost      = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update([
            'can_login'             => false,
            'invitation_token'      => 'some-hashed-token',
            'invitation_sent_at'    => now()->subHours(2),
            'invitation_expires_at' => now()->addDays(5),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('revokeInvitation', $ghost->id)
            ->assertDispatched('toast');

        $fresh = $ghost->fresh();
        $this->assertNull($fresh->invitation_token);
        $this->assertNull($fresh->invitation_sent_at);
        $this->assertNull($fresh->invitation_expires_at);
    }

    public function test_revoke_invitation_guard_requires_own_grower(): void
    {
        $supervisor      = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();
        $ghost           = $this->makeViticulturistForSupervisor($otherSupervisor);
        $ghost->update(['can_login' => false]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('revokeInvitation', $ghost->id);
    }

    public function test_revoke_invitation_rejects_active_grower(): void
    {
        $supervisor  = $this->makeSupervisor();
        $activeGrower = $this->makeViticulturistForSupervisor($supervisor);
        $activeGrower->update(['can_login' => true]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('revokeInvitation', $activeGrower->id);
    }

    // ── search ────────────────────────────────────────────────────────────────

    public function test_search_filters_growers_by_name(): void
    {
        $supervisor = $this->makeSupervisor();

        $visible = $this->makeViticulturistForSupervisor($supervisor);
        $visible->update(['name' => 'Luis Montaña']);

        $hidden = $this->makeViticulturistForSupervisor($supervisor);
        $hidden->update(['name' => 'Pedro Llano']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('search', 'montaña')
            ->assertSee('Luis Montaña')
            ->assertDontSee('Pedro Llano');
    }

    public function test_clear_search_resets_filter(): void
    {
        $supervisor = $this->makeSupervisor();

        $grower = $this->makeViticulturistForSupervisor($supervisor);
        $grower->update(['name' => 'Ana Bodega']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('search', 'xyz')
            ->assertDontSee('Ana Bodega')
            ->call('clearSearch')
            ->assertSet('search', '')
            ->assertSee('Ana Bodega');
    }

    public function test_growers_from_another_supervisor_not_visible(): void
    {
        $supervisor      = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();

        $other = $this->makeViticulturistForSupervisor($otherSupervisor);
        $other->update(['name' => 'Viticultor Ajeno']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertDontSee('Viticultor Ajeno');
    }
}
