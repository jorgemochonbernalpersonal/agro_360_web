<?php

namespace Tests\Feature\Viticulturist\WineryAccess;

use App\Livewire\Viticulturist\WineryAccess\Index;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\NotebookAccessRespondedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class NotebookAccessTest extends ViticulturistTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeWinery(): User
    {
        return User::factory()->create([
            'role'              => 'winery',
            'email_verified_at' => now(),
        ]);
    }

    private function makeSupervisor(): User
    {
        return User::factory()->create([
            'role'              => 'supervisor',
            'email_verified_at' => now(),
        ]);
    }

    private function linkWineryViticulturist(User $winery, User $viticulturist): WineryViticulturist
    {
        return WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);
    }

    private function linkSupervisorViticulturist(User $supervisor, User $viticulturist): SupervisorViticulturist
    {
        return SupervisorViticulturist::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'assigned_by'      => $supervisor->id,
        ]);
    }

    private function pendingWineryRequest(User $winery, User $viticulturist): NotebookAccessRequest
    {
        return NotebookAccessRequest::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);
    }

    private function pendingSupervisorRequest(User $supervisor, User $viticulturist): NotebookAccessRequest
    {
        return NotebookAccessRequest::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_component_renders_for_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertOk();
    }

    public function test_pending_winery_request_appears_in_pending_list(): void
    {
        $viticulturist = $this->makeViticulturist();
        $winery        = $this->makeWinery();

        $this->linkWineryViticulturist($winery, $viticulturist);
        $request = $this->pendingWineryRequest($winery, $viticulturist);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertViewHas('pending', fn($p) => $p->contains('id', $request->id));
    }

    public function test_pending_supervisor_request_appears_in_pending_list(): void
    {
        $viticulturist = $this->makeViticulturist();
        $supervisor    = $this->makeSupervisor();

        $this->linkSupervisorViticulturist($supervisor, $viticulturist);
        $request = $this->pendingSupervisorRequest($supervisor, $viticulturist);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertViewHas('pending', fn($p) => $p->contains('id', $request->id));
    }

    // ── aprobar solicitud de bodega ───────────────────────────────────────────

    public function test_viticulturist_can_approve_winery_request(): void
    {
        Notification::fake();

        $viticulturist = $this->makeViticulturist();
        $winery        = $this->makeWinery();
        $relation      = $this->linkWineryViticulturist($winery, $viticulturist);
        $request       = $this->pendingWineryRequest($winery, $viticulturist);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('approve', $request->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('notebook_access_requests', [
            'id'     => $request->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('winery_viticulturist', [
            'id'              => $relation->id,
            'notebook_access' => true,
        ]);

        Notification::assertSentTo($winery, NotebookAccessRespondedNotification::class);
    }

    public function test_approve_sets_notebook_granted_at_timestamp(): void
    {
        $viticulturist = $this->makeViticulturist();
        $winery        = $this->makeWinery();
        $relation      = $this->linkWineryViticulturist($winery, $viticulturist);
        $request       = $this->pendingWineryRequest($winery, $viticulturist);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('approve', $request->id);

        $this->assertNotNull($relation->fresh()->notebook_granted_at);
    }

    // ── rechazar solicitud de bodega ──────────────────────────────────────────

    public function test_viticulturist_can_reject_winery_request(): void
    {
        Notification::fake();

        $viticulturist = $this->makeViticulturist();
        $winery        = $this->makeWinery();
        $this->linkWineryViticulturist($winery, $viticulturist);
        $request = $this->pendingWineryRequest($winery, $viticulturist);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('reject', $request->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('notebook_access_requests', [
            'id'     => $request->id,
            'status' => NotebookAccessRequest::STATUS_REJECTED,
        ]);

        Notification::assertSentTo($winery, NotebookAccessRespondedNotification::class);
    }

    public function test_reject_does_not_grant_notebook_access(): void
    {
        $viticulturist = $this->makeViticulturist();
        $winery        = $this->makeWinery();
        $relation      = $this->linkWineryViticulturist($winery, $viticulturist);
        $request       = $this->pendingWineryRequest($winery, $viticulturist);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('reject', $request->id);

        $this->assertFalse((bool) $relation->fresh()->notebook_access);
    }

    // ── revocar acceso de bodega ──────────────────────────────────────────────

    public function test_viticulturist_can_revoke_winery_access(): void
    {
        Notification::fake();

        $viticulturist = $this->makeViticulturist();
        $winery        = $this->makeWinery();
        $relation      = $this->linkWineryViticulturist($winery, $viticulturist);

        $relation->update([
            'notebook_access'    => true,
            'notebook_granted_at' => now(),
        ]);

        NotebookAccessRequest::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at'     => now()->subDay(),
            'responded_at'     => now()->subDay(),
        ]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('revoke', $winery->id, 'winery')
            ->assertDispatched('toast');

        $this->assertFalse((bool) $relation->fresh()->notebook_access);
        $this->assertNotNull($relation->fresh()->notebook_revoked_at);

        $this->assertDatabaseHas('notebook_access_requests', [
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_REJECTED,
        ]);

        Notification::assertSentTo($winery, NotebookAccessRespondedNotification::class);
    }

    // ── aprobar solicitud de supervisor/DO ────────────────────────────────────

    public function test_viticulturist_can_approve_supervisor_request(): void
    {
        Notification::fake();

        $viticulturist = $this->makeViticulturist();
        $supervisor    = $this->makeSupervisor();
        $relation      = $this->linkSupervisorViticulturist($supervisor, $viticulturist);
        $request       = $this->pendingSupervisorRequest($supervisor, $viticulturist);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('approve', $request->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('notebook_access_requests', [
            'id'     => $request->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('supervisor_viticulturist', [
            'id'              => $relation->id,
            'notebook_access' => true,
        ]);

        Notification::assertSentTo($supervisor, NotebookAccessRespondedNotification::class);
    }

    // ── revocar acceso de supervisor/DO ──────────────────────────────────────

    public function test_viticulturist_can_revoke_supervisor_access(): void
    {
        Notification::fake();

        $viticulturist = $this->makeViticulturist();
        $supervisor    = $this->makeSupervisor();
        $relation      = $this->linkSupervisorViticulturist($supervisor, $viticulturist);

        $relation->update([
            'notebook_access'    => true,
            'notebook_granted_at' => now(),
        ]);

        NotebookAccessRequest::create([
            'supervisor_id'    => $supervisor->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at'     => now()->subDay(),
            'responded_at'     => now()->subDay(),
        ]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('revoke', $supervisor->id, 'supervisor')
            ->assertDispatched('toast');

        $this->assertFalse((bool) $relation->fresh()->notebook_access);
        $this->assertNotNull($relation->fresh()->notebook_revoked_at);

        Notification::assertSentTo($supervisor, NotebookAccessRespondedNotification::class);
    }

    // ── guards: aislamiento ───────────────────────────────────────────────────

    public function test_viticulturist_cannot_approve_another_viticulturists_request(): void
    {
        $viticulturist1 = $this->makeViticulturist();
        $viticulturist2 = $this->makeOtherViticulturist();
        $winery         = $this->makeWinery();

        $this->linkWineryViticulturist($winery, $viticulturist2);
        $request = $this->pendingWineryRequest($winery, $viticulturist2);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($viticulturist1)
            ->test(Index::class)
            ->call('approve', $request->id);
    }

    public function test_viticulturist_cannot_reject_another_viticulturists_request(): void
    {
        $viticulturist1 = $this->makeViticulturist();
        $viticulturist2 = $this->makeOtherViticulturist();
        $winery         = $this->makeWinery();

        $this->linkWineryViticulturist($winery, $viticulturist2);
        $request = $this->pendingWineryRequest($winery, $viticulturist2);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($viticulturist1)
            ->test(Index::class)
            ->call('reject', $request->id);
    }

    public function test_cannot_approve_already_approved_request(): void
    {
        $viticulturist = $this->makeViticulturist();
        $winery        = $this->makeWinery();
        $this->linkWineryViticulturist($winery, $viticulturist);

        $request = NotebookAccessRequest::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at'     => now()->subDay(),
            'responded_at'     => now()->subDay(),
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->call('approve', $request->id);
    }

    // ── granted list ─────────────────────────────────────────────────────────

    public function test_granted_winery_access_appears_in_granted_list(): void
    {
        $viticulturist = $this->makeViticulturist();
        $winery        = $this->makeWinery();
        $relation      = $this->linkWineryViticulturist($winery, $viticulturist);

        $relation->update([
            'notebook_access'    => true,
            'notebook_granted_at' => now(),
        ]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertViewHas('granted', fn($g) =>
                $g->contains(fn($item) => $item->id === $winery->id && $item->type === 'winery')
            );
    }

    public function test_rejected_request_appears_in_rejected_list(): void
    {
        $viticulturist = $this->makeViticulturist();
        $winery        = $this->makeWinery();
        $this->linkWineryViticulturist($winery, $viticulturist);

        $request = NotebookAccessRequest::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_REJECTED,
            'requested_at'     => now()->subDay(),
            'responded_at'     => now(),
        ]);

        Livewire::actingAs($viticulturist)
            ->test(Index::class)
            ->assertViewHas('rejected', fn($r) => $r->contains('id', $request->id));
    }
}
