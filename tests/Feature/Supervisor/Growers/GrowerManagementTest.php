<?php

namespace Tests\Feature\Supervisor\Growers;

use App\Livewire\Supervisor\Growers\Index;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
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

    // ── removeGrower ──────────────────────────────────────────────────────────

    public function test_supervisor_can_remove_grower_from_pool(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('removeGrower', $viticulturist->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('supervisor_viticulturist', [
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
        ]);
    }

    public function test_remove_grower_also_cleans_winery_assignments_by_this_supervisor(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist         = $this->makeViticulturistForSupervisor($supervisor);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $supervisor->id,
            'assigned_by'      => $supervisor->id,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('removeGrower', $viticulturist->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('winery_viticulturist', [
            'viticulturist_id' => $viticulturist->id,
            'supervisor_id'    => $supervisor->id,
        ]);
    }

    public function test_remove_grower_cleans_pending_notebook_access_requests(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);
        $viticulturist->update(['can_login' => true]);

        NotebookAccessRequest::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('removeGrower', $viticulturist->id);

        $this->assertDatabaseCount('notebook_access_requests', 0);
    }

    public function test_remove_grower_does_not_delete_viticulturist_account(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('removeGrower', $viticulturist->id);

        $this->assertDatabaseHas('users', ['id' => $viticulturist->id]);
    }

    public function test_remove_grower_does_not_affect_winery_own_assignments(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist         = $this->makeViticulturistForSupervisor($supervisor);

        // Own assignment (source=own, no supervisor_id) must survive
        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('removeGrower', $viticulturist->id);

        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
        ]);
    }

    public function test_supervisor_cannot_remove_grower_from_another_supervisor(): void
    {
        $supervisor      = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();
        $viticulturist   = $this->makeViticulturistForSupervisor($otherSupervisor);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('removeGrower', $viticulturist->id);
    }

    public function test_removed_grower_disappears_from_list(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);
        $viticulturist->update(['name' => 'Eliminado del Pool']);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertSee('Eliminado del Pool')
            ->call('removeGrower', $viticulturist->id)
            ->assertDontSee('Eliminado del Pool');
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

    // ── statusFilter ──────────────────────────────────────────────────────────

    public function test_status_filter_active_shows_only_active_growers(): void
    {
        $supervisor = $this->makeSupervisor();

        $active = $this->makeViticulturistForSupervisor($supervisor);
        $active->update(['name' => 'Activo Uno', 'can_login' => true]);

        $ghost = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update(['name' => 'Ghost Uno', 'can_login' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('setStatusFilter', 'active')
            ->assertSee('Activo Uno')
            ->assertDontSee('Ghost Uno');
    }

    public function test_status_filter_ghost_shows_only_non_invited_ghosts(): void
    {
        $supervisor = $this->makeSupervisor();

        $ghost = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update([
            'name'             => 'Ghost Puro',
            'can_login'        => false,
            'invitation_token' => null,
        ]);

        $invited = $this->makeViticulturistForSupervisor($supervisor);
        $invited->update([
            'name'                  => 'Invitado Uno',
            'can_login'             => false,
            'invitation_token'      => 'some-token',
            'invitation_expires_at' => now()->addDays(5),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('setStatusFilter', 'ghost')
            ->assertSee('Ghost Puro')
            ->assertDontSee('Invitado Uno');
    }

    public function test_status_filter_invited_shows_only_pending_invitation_growers(): void
    {
        $supervisor = $this->makeSupervisor();

        $invited = $this->makeViticulturistForSupervisor($supervisor);
        $invited->update([
            'name'                  => 'Invitado Activo',
            'can_login'             => false,
            'invitation_token'      => 'some-token',
            'invitation_expires_at' => now()->addDays(3),
        ]);

        $ghost = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update([
            'name'             => 'Ghost Sin Invitar',
            'can_login'        => false,
            'invitation_token' => null,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('setStatusFilter', 'invited')
            ->assertSee('Invitado Activo')
            ->assertDontSee('Ghost Sin Invitar');
    }

    public function test_status_filter_all_shows_all_growers(): void
    {
        $supervisor = $this->makeSupervisor();

        $active = $this->makeViticulturistForSupervisor($supervisor);
        $active->update(['name' => 'Activo Mix', 'can_login' => true]);

        $ghost = $this->makeViticulturistForSupervisor($supervisor);
        $ghost->update(['name' => 'Ghost Mix', 'can_login' => false]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('setStatusFilter', '')
            ->assertSee('Activo Mix')
            ->assertSee('Ghost Mix');
    }

    public function test_status_filter_expired_invitation_counted_as_ghost(): void
    {
        $supervisor = $this->makeSupervisor();

        $expiredInvite = $this->makeViticulturistForSupervisor($supervisor);
        $expiredInvite->update([
            'name'                  => 'Invitacion Caducada',
            'can_login'             => false,
            'invitation_token'      => 'old-token',
            'invitation_expires_at' => now()->subDays(1),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('setStatusFilter', 'ghost')
            ->assertSee('Invitacion Caducada');
    }

    public function test_set_status_filter_resets_pagination(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('setStatusFilter', 'active')
            ->assertSet('statusFilter', 'active');
    }

    // ── linkExistingGrower ────────────────────────────────────────────────────

    public function test_supervisor_can_link_existing_active_viticulturist(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => true,
            'name'      => 'Viticultor Externo',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openLinkModal')
            ->call('selectLinkCandidate', $viticulturist->id)
            ->call('linkExistingGrower')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('supervisor_viticulturist', [
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
        ]);
    }

    public function test_link_grower_requires_selection(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openLinkModal')
            ->call('linkExistingGrower')
            ->assertHasErrors(['linkSelectedId']);
    }

    public function test_link_grower_rejects_already_in_pool(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);
        $viticulturist->update(['can_login' => true]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openLinkModal')
            ->set('linkSelectedId', $viticulturist->id)
            ->call('linkExistingGrower')
            ->assertDispatched('toast');

        // Still only one entry
        $this->assertDatabaseCount('supervisor_viticulturist', 1);
    }

    public function test_link_grower_rejects_ghost_users(): void
    {
        $supervisor = $this->makeSupervisor();
        $ghost      = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => false,
            'name'      => 'Ghost Externo',
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openLinkModal')
            ->set('linkSelectedId', $ghost->id)
            ->call('linkExistingGrower');
    }

    public function test_search_link_candidates_excludes_pool_members(): void
    {
        $supervisor    = $this->makeSupervisor();
        $inPool        = $this->makeViticulturistForSupervisor($supervisor);
        $inPool->update(['name' => 'Ya En Pool', 'can_login' => true]);

        $outside = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => true,
            'name'      => 'Fuera Del Pool',
        ]);

        $component = Livewire::actingAs($supervisor)->test(Index::class);
        $component->set('showLinkModal', true)->set('linkQuery', 'Pool');

        $candidates = $component->instance()->searchLinkCandidates();
        $names      = collect($candidates)->pluck('name');

        $this->assertContains('Fuera Del Pool', $names->toArray());
        $this->assertNotContains('Ya En Pool', $names->toArray());
    }

    public function test_search_link_candidates_excludes_ghosts(): void
    {
        $supervisor = $this->makeSupervisor();
        $ghost      = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => false,
            'name'      => 'Ghost Oculto',
        ]);

        $component = Livewire::actingAs($supervisor)->test(Index::class);
        $component->set('showLinkModal', true)->set('linkQuery', 'Ghost');

        $candidates = $component->instance()->searchLinkCandidates();
        $names      = collect($candidates)->pluck('name');

        $this->assertNotContains('Ghost Oculto', $names->toArray());
    }

    public function test_search_link_candidates_returns_empty_for_short_query(): void
    {
        $supervisor = $this->makeSupervisor();

        $component = Livewire::actingAs($supervisor)->test(Index::class);
        $component->set('showLinkModal', true)->set('linkQuery', 'A');

        $candidates = $component->instance()->searchLinkCandidates();
        $this->assertEmpty($candidates);
    }

    public function test_open_link_modal_resets_state(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('linkQuery', 'old query')
            ->set('linkSelectedId', 99)
            ->call('openLinkModal')
            ->assertSet('linkQuery', '')
            ->assertSet('linkSelectedId', null)
            ->assertSet('showLinkModal', true);
    }

    public function test_linked_viticulturist_appears_in_growers_list(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => true,
            'name'      => 'Recien Vinculado',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openLinkModal')
            ->call('selectLinkCandidate', $viticulturist->id)
            ->call('linkExistingGrower')
            ->assertSee('Recien Vinculado');
    }
}
