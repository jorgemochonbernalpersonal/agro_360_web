<?php

namespace Tests\Feature\Winery;

use App\Livewire\Winery\Viticulturists\Show;
use App\Models\NotebookAccessRequest;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\NotebookAccessRequestedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class NotebookAccessTest extends WineryTestCase
{
    protected User $winery;

    protected User $viticulturist;

    protected WineryViticulturist $relation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = $this->makeWinery();

        $this->viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'can_login' => true,
        ]);

        $this->relation = WineryViticulturist::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $this->winery->id,
        ]);

        $this->actingAs($this->winery);
    }

    // ── requestNotebookAccess ─────────────────────────────────────────────────

    public function test_winery_can_request_notebook_access(): void
    {
        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('requestNotebookAccess')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notebook_access_requests', [
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
        ]);
    }

    public function test_duplicate_pending_request_shows_info_toast(): void
    {
        NotebookAccessRequest::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('requestNotebookAccess');

        // Only one record should exist — no duplicate
        $this->assertDatabaseCount('notebook_access_requests', 1);
    }

    public function test_request_sends_notification_to_viticulturist(): void
    {
        Notification::fake();

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('requestNotebookAccess');

        Notification::assertSentTo(
            $this->viticulturist,
            NotebookAccessRequestedNotification::class,
            fn ($n) => $n->toMail($this->viticulturist)->subject === 'Nueva solicitud de acceso al cuaderno — '.$this->winery->name,
        );
    }

    public function test_notification_not_sent_when_request_is_duplicate_pending(): void
    {
        Notification::fake();

        NotebookAccessRequest::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('requestNotebookAccess');

        Notification::assertNothingSent();
    }

    public function test_request_blocked_when_access_already_approved(): void
    {
        $this->relation->grantNotebookAccess();

        NotebookAccessRequest::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at' => now()->subDay(),
            'responded_at' => now()->subDay(),
        ]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('requestNotebookAccess')
            ->assertHasNoErrors();

        // Request must remain approved — not overwritten back to pending
        $this->assertDatabaseHas('notebook_access_requests', [
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
        ]);
    }

    public function test_winery_can_re_request_after_rejection(): void
    {
        NotebookAccessRequest::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_REJECTED,
            'requested_at' => now()->subDays(3),
            'responded_at' => now()->subDays(1),
        ]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('requestNotebookAccess')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notebook_access_requests', [
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
        ]);
    }

    // ── cancelAccessRequest ───────────────────────────────────────────────────

    public function test_winery_can_cancel_pending_request(): void
    {
        NotebookAccessRequest::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('cancelAccessRequest');

        $this->assertDatabaseEmpty('notebook_access_requests');
    }

    public function test_cancel_does_nothing_when_no_pending_request(): void
    {
        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('cancelAccessRequest')
            ->assertHasNoErrors();

        $this->assertDatabaseEmpty('notebook_access_requests');
    }

    public function test_cancel_does_not_delete_approved_request(): void
    {
        NotebookAccessRequest::create([
            'winery_id' => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_APPROVED,
            'requested_at' => now()->subDay(),
            'responded_at' => now(),
        ]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('cancelAccessRequest');

        $this->assertDatabaseCount('notebook_access_requests', 1);
    }

    // ── WineryViticulturist model methods ─────────────────────────────────────

    public function test_grant_notebook_access_sets_correct_fields(): void
    {
        $this->relation->grantNotebookAccess();
        $this->relation->refresh();

        $this->assertTrue($this->relation->notebook_access);
        $this->assertNotNull($this->relation->notebook_granted_at);
        $this->assertNull($this->relation->notebook_revoked_at);
    }

    public function test_revoke_notebook_access_sets_correct_fields(): void
    {
        $this->relation->grantNotebookAccess();
        $this->relation->revokeNotebookAccess();
        $this->relation->refresh();

        $this->assertFalse($this->relation->notebook_access);
        $this->assertNotNull($this->relation->notebook_revoked_at);
    }

    public function test_grant_clears_revoked_at(): void
    {
        $this->relation->update(['notebook_revoked_at' => now()->subDay()]);
        $this->relation->grantNotebookAccess();
        $this->relation->refresh();

        $this->assertNull($this->relation->notebook_revoked_at);
    }

    // ── NotebookAccessRequest model helpers ───────────────────────────────────

    public function test_is_pending_returns_true_for_pending_status(): void
    {
        $request = new NotebookAccessRequest(['status' => NotebookAccessRequest::STATUS_PENDING]);

        $this->assertTrue($request->isPending());
        $this->assertFalse($request->isApproved());
        $this->assertFalse($request->isRejected());
    }

    public function test_is_approved_returns_true_for_approved_status(): void
    {
        $request = new NotebookAccessRequest(['status' => NotebookAccessRequest::STATUS_APPROVED]);

        $this->assertFalse($request->isPending());
        $this->assertTrue($request->isApproved());
        $this->assertFalse($request->isRejected());
    }

    public function test_is_rejected_returns_true_for_rejected_status(): void
    {
        $request = new NotebookAccessRequest(['status' => NotebookAccessRequest::STATUS_REJECTED]);

        $this->assertFalse($request->isPending());
        $this->assertFalse($request->isApproved());
        $this->assertTrue($request->isRejected());
    }

    // ── Authorization ─────────────────────────────────────────────────────────

    public function test_other_winery_cannot_cancel_request_from_different_winery(): void
    {
        $otherWinery = $this->makeOtherWinery();

        NotebookAccessRequest::create([
            'winery_id' => $otherWinery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        // Acting as $this->winery — cancel should only affect own winery's requests
        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('cancelAccessRequest');

        // Other winery's request must remain untouched
        $this->assertDatabaseHas('notebook_access_requests', [
            'winery_id' => $otherWinery->id,
            'status' => NotebookAccessRequest::STATUS_PENDING,
        ]);
    }
}
