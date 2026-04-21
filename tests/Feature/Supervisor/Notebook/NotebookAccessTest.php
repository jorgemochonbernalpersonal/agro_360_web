<?php

namespace Tests\Feature\Supervisor\Notebook;

use App\Livewire\Supervisor\Notebook\Index;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Notifications\NotebookAccessRequestedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class NotebookAccessTest extends SupervisorTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    /** Viticulturist in supervisor pool with can_login=true (can grant access). */
    private function makeActiveViticulturistForSupervisor(User $supervisor): User
    {
        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
            'can_login'         => true,
        ]);

        SupervisorViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by'      => $supervisor->id,
        ]);

        return $viticulturist;
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_component_renders_for_supervisor(): void
    {
        $supervisor = $this->makeSupervisor();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertOk();
    }

    // ── solicitar acceso ──────────────────────────────────────────────────────

    public function test_supervisor_can_request_notebook_access(): void
    {
        Notification::fake();

        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeActiveViticulturistForSupervisor($supervisor);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openRequestModal')
            ->set('targetViticulturistId', $viticulturist->id)
            ->call('requestAccess')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('notebook_access_requests', [
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
        ]);

        Notification::assertSentTo($viticulturist, NotebookAccessRequestedNotification::class);
    }

    public function test_supervisor_cannot_request_for_viticulturist_not_in_pool(): void
    {
        $supervisor    = $this->makeSupervisor();
        $outsideVit    = User::factory()->create(['role' => 'viticulturist', 'can_login' => true]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openRequestModal')
            ->set('targetViticulturistId', $outsideVit->id)
            ->call('requestAccess');
    }

    public function test_supervisor_cannot_request_duplicate_pending(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeActiveViticulturistForSupervisor($supervisor);

        NotebookAccessRequest::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openRequestModal')
            ->set('targetViticulturistId', $viticulturist->id)
            ->call('requestAccess')
            ->assertDispatched('toast');

        // Still only one record
        $this->assertSame(1, NotebookAccessRequest::where('supervisor_id', $supervisor->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->count());
    }

    public function test_supervisor_cannot_request_when_already_approved(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeActiveViticulturistForSupervisor($supervisor);

        NotebookAccessRequest::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at'     => now()->subDay(),
            'responded_at'     => now()->subDay(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openRequestModal')
            ->set('targetViticulturistId', $viticulturist->id)
            ->call('requestAccess')
            ->assertDispatched('toast');

        $this->assertSame(1, NotebookAccessRequest::where('supervisor_id', $supervisor->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->count());
    }

    // ── revocar acceso ────────────────────────────────────────────────────────

    public function test_supervisor_can_revoke_approved_access(): void
    {
        $supervisor    = $this->makeSupervisor();
        $viticulturist = $this->makeActiveViticulturistForSupervisor($supervisor);

        $relation = SupervisorViticulturist::where('supervisor_id', $supervisor->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->first();

        $relation->update([
            'notebook_access'     => true,
            'notebook_granted_at' => now()->subDay(),
        ]);

        $request = NotebookAccessRequest::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at'     => now()->subDay(),
            'responded_at'     => now()->subDay(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('revokeAccess', $request->id)
            ->assertDispatched('toast');

        $this->assertFalse((bool) $relation->fresh()->notebook_access);
        $this->assertDatabaseMissing('notebook_access_requests', ['id' => $request->id]);
    }

    public function test_supervisor_cannot_revoke_another_supervisors_request(): void
    {
        $supervisor1   = $this->makeSupervisor();
        $supervisor2   = $this->makeSupervisor();
        $viticulturist = $this->makeActiveViticulturistForSupervisor($supervisor1);

        $request = NotebookAccessRequest::create([
            'supervisor_id'    => $supervisor1->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at'     => now()->subDay(),
            'responded_at'     => now()->subDay(),
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor2)
            ->test(Index::class)
            ->call('revokeAccess', $request->id);
    }

    // ── stats ─────────────────────────────────────────────────────────────────

    public function test_stats_count_requests_by_status(): void
    {
        $supervisor = $this->makeSupervisor();

        $vit1 = $this->makeActiveViticulturistForSupervisor($supervisor);
        $vit2 = $this->makeActiveViticulturistForSupervisor($supervisor);
        $vit3 = $this->makeActiveViticulturistForSupervisor($supervisor);

        NotebookAccessRequest::create(['supervisor_id' => $supervisor->id, 'viticulturist_id' => $vit1->id, 'status' => 'pending',  'requested_at' => now()]);
        NotebookAccessRequest::create(['supervisor_id' => $supervisor->id, 'viticulturist_id' => $vit2->id, 'status' => 'approved', 'requested_at' => now()->subDay(), 'responded_at' => now()->subDay()]);
        NotebookAccessRequest::create(['supervisor_id' => $supervisor->id, 'viticulturist_id' => $vit3->id, 'status' => 'rejected', 'requested_at' => now()->subDay(), 'responded_at' => now()->subDay()]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->assertViewHas('stats', fn($s) =>
                $s['total'] === 3 && $s['pending'] === 1 && $s['approved'] === 1 && $s['rejected'] === 1
            );
    }

    // ── available for request (modal) ─────────────────────────────────────────

    public function test_ghost_viticulturists_excluded_from_available_list(): void
    {
        $supervisor = $this->makeSupervisor();

        // Ghost: can_login=false — cannot approve requests
        $ghost = User::factory()->create(['role' => 'viticulturist', 'can_login' => false]);
        SupervisorViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $ghost->id,
            'assigned_by'      => $supervisor->id,
        ]);

        $active = $this->makeActiveViticulturistForSupervisor($supervisor);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openRequestModal')
            ->assertViewHas('availableForRequest', fn($list) =>
                $list->contains('id', $active->id) && !$list->contains('id', $ghost->id)
            );
    }

    public function test_already_requested_viticulturist_excluded_from_available_list(): void
    {
        $supervisor = $this->makeSupervisor();
        $vit        = $this->makeActiveViticulturistForSupervisor($supervisor);

        NotebookAccessRequest::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $vit->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('openRequestModal')
            ->assertViewHas('availableForRequest', fn($list) => !$list->contains('id', $vit->id));
    }

    // ── filter ────────────────────────────────────────────────────────────────

    public function test_filter_by_status(): void
    {
        $supervisor = $this->makeSupervisor();
        $vit1       = $this->makeActiveViticulturistForSupervisor($supervisor);
        $vit2       = $this->makeActiveViticulturistForSupervisor($supervisor);

        $r1 = NotebookAccessRequest::create(['supervisor_id' => $supervisor->id, 'viticulturist_id' => $vit1->id, 'status' => 'pending',  'requested_at' => now()]);
        $r2 = NotebookAccessRequest::create(['supervisor_id' => $supervisor->id, 'viticulturist_id' => $vit2->id, 'status' => 'approved', 'requested_at' => now()->subDay(), 'responded_at' => now()]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('filterStatus', 'pending')
            ->assertViewHas('requests', fn($p) =>
                $p->contains('id', $r1->id) && !$p->contains('id', $r2->id)
            );
    }
}
