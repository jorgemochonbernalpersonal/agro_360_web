<?php

namespace Tests\Feature\Supervisor\Notebook;

use App\Livewire\Supervisor\Notebook\Index as NotebookIndex;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

/**
 * Tests for the Supervisor Notebook Access management panel.
 *
 * Covers: access control, requesting access, revocation, scoping,
 * filtering, and duplicate-request protection.
 */
class IndexTest extends SupervisorTestCase
{
    // ── access ────────────────────────────────────────────────────────────────

    public function test_supervisor_can_access_notebook_access_page(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.notebook.index'))
            ->assertOk();
    }

    public function test_winery_cannot_access_notebook_access_page(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('supervisor.notebook.index'))
            ->assertForbidden();
    }

    // ── request access ────────────────────────────────────────────────────────

    public function test_supervisor_can_request_notebook_access_for_own_viticulturist(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist = $this->makeViticulturistForDo($supervisor, $winery);

        $this->actingAs($supervisor);

        Livewire::test(NotebookIndex::class)
            ->set('showRequestModal', true)
            ->set('targetViticulturistId', $viticulturist->id)
            ->call('requestAccess');

        $this->assertDatabaseHas('notebook_access_requests', [
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
        ]);
    }

    public function test_duplicate_pending_request_is_blocked(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist = $this->makeViticulturistForDo($supervisor, $winery);

        NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(NotebookIndex::class)
            ->set('showRequestModal', true)
            ->set('targetViticulturistId', $viticulturist->id)
            ->call('requestAccess');

        $this->assertSame(1, NotebookAccessRequest::where('supervisor_id', $supervisor->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->count()
        );
    }

    public function test_duplicate_approved_request_is_blocked(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist = $this->makeViticulturistForDo($supervisor, $winery);

        NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at' => now(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(NotebookIndex::class)
            ->set('showRequestModal', true)
            ->set('targetViticulturistId', $viticulturist->id)
            ->call('requestAccess');

        $this->assertSame(1, NotebookAccessRequest::where('supervisor_id', $supervisor->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->count()
        );
    }

    public function test_new_request_allowed_after_previous_rejection(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $viticulturist = $this->makeViticulturistForDo($supervisor, $winery);

        NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_REJECTED,
            'requested_at' => now()->subDays(7),
            'responded_at' => now()->subDays(5),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(NotebookIndex::class)
            ->set('showRequestModal', true)
            ->set('targetViticulturistId', $viticulturist->id)
            ->call('requestAccess');

        $this->assertDatabaseHas('notebook_access_requests', [
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
        ]);
        $this->assertSame(1, NotebookAccessRequest::where('supervisor_id', $supervisor->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->count()
        );
    }

    // ── revocation ────────────────────────────────────────────────────────────

    public function test_supervisor_can_revoke_own_request(): void
    {
        $supervisor = $this->makeSupervisor();
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        SupervisorViticulturist::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by' => $supervisor->id,
        ]);

        $request = NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at' => now(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(NotebookIndex::class)
            ->call('revokeAccess', $request->id);

        $this->assertDatabaseMissing('notebook_access_requests', ['id' => $request->id]);
    }

    public function test_supervisor_cannot_revoke_another_supervisors_request(): void
    {
        $supervisor = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $request = NotebookAccessRequest::create([
            'supervisor_id' => $otherSupervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at' => now(),
        ]);

        $this->actingAs($supervisor);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(NotebookIndex::class)
            ->call('revokeAccess', $request->id);
    }

    // ── scoping ───────────────────────────────────────────────────────────────

    public function test_list_shows_only_own_requests(): void
    {
        $supervisor = $this->makeSupervisor();
        $otherSupervisor = $this->makeSupervisor();
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $ownRequest = NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        $otherRequest = NotebookAccessRequest::create([
            'supervisor_id' => $otherSupervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(NotebookIndex::class)
            ->assertViewHas('requests', fn ($requests) => $requests->contains('id', $ownRequest->id) &&
                ! $requests->contains('id', $otherRequest->id)
            );
    }

    // ── stats ─────────────────────────────────────────────────────────────────

    public function test_stats_reflect_own_requests_by_status(): void
    {
        $supervisor = $this->makeSupervisor();
        $vit1 = User::factory()->create(['role' => 'viticulturist']);
        $vit2 = User::factory()->create(['role' => 'viticulturist']);

        NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $vit1->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);
        NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $vit2->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at' => now(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(NotebookIndex::class)
            ->assertViewHas('stats', fn ($stats) => $stats['total'] === 2 &&
                $stats['pending'] === 1 &&
                $stats['approved'] === 1 &&
                $stats['rejected'] === 0
            );
    }

    // ── filtering ─────────────────────────────────────────────────────────────

    public function test_filter_by_pending_status(): void
    {
        $supervisor = $this->makeSupervisor();
        $vit1 = User::factory()->create(['role' => 'viticulturist']);
        $vit2 = User::factory()->create(['role' => 'viticulturist']);

        $pending = NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $vit1->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);
        $approved = NotebookAccessRequest::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $vit2->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at' => now(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(NotebookIndex::class)
            ->set('filterStatus', 'pending')
            ->assertViewHas('requests', fn ($requests) => $requests->contains('id', $pending->id) &&
                ! $requests->contains('id', $approved->id)
            );
    }

    // ── validation ────────────────────────────────────────────────────────────

    public function test_request_requires_target_viticulturist(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor);

        Livewire::test(NotebookIndex::class)
            ->set('showRequestModal', true)
            ->call('requestAccess')
            ->assertHasErrors(['targetViticulturistId']);
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturistForDo(User $supervisor, User $winery): User
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'can_login' => true,
            'email_verified_at' => now(),
        ]);

        SupervisorViticulturist::create([
            'supervisor_id' => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by' => $supervisor->id,
        ]);

        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id' => $supervisor->id,
            'assigned_by' => $supervisor->id,
        ]);

        return $viticulturist;
    }
}
