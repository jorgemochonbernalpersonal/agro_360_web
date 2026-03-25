<?php

namespace Tests\Feature\Viticulturist;

use App\Livewire\Viticulturist\WineryAccess\Index;
use App\Models\NotebookAccessRequest;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\NotebookAccessRespondedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class WineryAccessTest extends ViticulturistTestCase
{
    protected User $viticulturist;
    protected User $winery;
    protected WineryViticulturist $relation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viticulturist = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => true,
        ]);

        $this->winery = User::factory()->create(['role' => 'winery']);

        $this->relation = WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        $this->actingAs($this->viticulturist);
    }

    // ── approve ───────────────────────────────────────────────────────────────

    public function test_viticulturist_can_approve_pending_request(): void
    {
        $request = NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::test(Index::class)
            ->call('approve', $request->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notebook_access_requests', [
            'id'     => $request->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'cuaderno_access'  => true,
        ]);
    }

    public function test_approve_sets_responded_at(): void
    {
        $request = NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::test(Index::class)->call('approve', $request->id);

        $this->assertNotNull($request->fresh()->responded_at);
    }

    public function test_reject_sets_responded_at(): void
    {
        $request = NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::test(Index::class)->call('reject', $request->id);

        $this->assertNotNull($request->fresh()->responded_at);
    }

    public function test_approve_sends_notification_to_winery(): void
    {
        Notification::fake();

        $request = NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::test(Index::class)->call('approve', $request->id);

        Notification::assertSentTo(
            $this->winery,
            NotebookAccessRespondedNotification::class,
            fn ($n) => $n->toArray($this->winery)['status'] === NotebookAccessRequest::STATUS_APPROVED,
        );
    }

    public function test_reject_sends_notification_to_winery(): void
    {
        Notification::fake();

        $request = NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::test(Index::class)->call('reject', $request->id);

        Notification::assertSentTo(
            $this->winery,
            NotebookAccessRespondedNotification::class,
            fn ($n) => $n->toArray($this->winery)['status'] === NotebookAccessRequest::STATUS_REJECTED,
        );
    }

    public function test_revoke_sends_notification_to_winery(): void
    {
        Notification::fake();

        $this->relation->grantNotebookAccess();

        NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at'     => now()->subDay(),
            'responded_at'     => now()->subDay(),
        ]);

        Livewire::test(Index::class)->call('revoke', $this->winery->id);

        Notification::assertSentTo(
            $this->winery,
            NotebookAccessRespondedNotification::class,
            fn ($n) => $n->toArray($this->winery)['status'] === NotebookAccessRequest::STATUS_REJECTED,
        );
    }

    public function test_viticulturist_cannot_approve_another_viticulturists_request(): void
    {
        $otherViticulturist = $this->makeOtherViticulturist();
        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $otherViticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        $request = NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $otherViticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        // Acting as $this->viticulturist — component guards with firstOrFail on viticulturist_id
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)->call('approve', $request->id);
    }

    // ── reject ────────────────────────────────────────────────────────────────

    public function test_viticulturist_can_reject_pending_request(): void
    {
        $request = NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::test(Index::class)
            ->call('reject', $request->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notebook_access_requests', [
            'id'     => $request->id,
            'status' => NotebookAccessRequest::STATUS_REJECTED,
        ]);
    }

    public function test_reject_does_not_grant_notebook_access(): void
    {
        $request = NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::test(Index::class)->call('reject', $request->id);

        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'cuaderno_access'  => false,
        ]);
    }

    public function test_viticulturist_cannot_reject_another_viticulturists_request(): void
    {
        $otherViticulturist = $this->makeOtherViticulturist();
        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $otherViticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        $request = NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $otherViticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)->call('reject', $request->id);
    }

    // ── revoke ────────────────────────────────────────────────────────────────

    public function test_viticulturist_can_revoke_granted_access(): void
    {
        $this->relation->grantNotebookAccess();

        NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at'     => now()->subDay(),
            'responded_at'     => now()->subDay(),
        ]);

        Livewire::test(Index::class)
            ->call('revoke', $this->winery->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'cuaderno_access'  => false,
        ]);
    }

    public function test_revoke_marks_request_as_rejected(): void
    {
        $this->relation->grantNotebookAccess();

        NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at'     => now()->subDay(),
            'responded_at'     => now()->subDay(),
        ]);

        Livewire::test(Index::class)->call('revoke', $this->winery->id);

        $this->assertDatabaseHas('notebook_access_requests', [
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_REJECTED,
        ]);
    }

    public function test_viticulturist_cannot_revoke_access_for_unrelated_winery(): void
    {
        $otherWinery = User::factory()->create(['role' => 'winery']);

        // No relation with cuaderno_access=true exists — component guards with firstOrFail
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)->call('revoke', $otherWinery->id);
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_index_renders_with_pending_requests(): void
    {
        NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::test(Index::class)
            ->assertSee($this->winery->name);
    }

    public function test_index_renders_granted_accesses(): void
    {
        $this->relation->grantNotebookAccess();

        Livewire::test(Index::class)
            ->assertSee($this->winery->name);
    }

    public function test_index_renders_empty_state_when_no_accesses(): void
    {
        Livewire::test(Index::class)
            ->assertSee('Ninguna bodega ni denominación tiene acceso');
    }
}
