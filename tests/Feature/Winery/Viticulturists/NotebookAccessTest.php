<?php

namespace Tests\Feature\Winery\Viticulturists;

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
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeViticulturistForWinery(User $winery, array $attrs = []): User
    {
        $viticulturist = User::factory()->create(array_merge([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
            'can_login'         => true,
        ], $attrs));

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        return $viticulturist;
    }

    // ── solicitar acceso ──────────────────────────────────────────────────────

    public function test_winery_can_request_notebook_access(): void
    {
        Notification::fake();

        $winery        = $this->makeWinery();
        $viticulturist = $this->makeViticulturistForWinery($winery);

        Livewire::actingAs($winery)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->call('requestNotebookAccess')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('notebook_access_requests', [
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
        ]);

        Notification::assertSentTo($viticulturist, NotebookAccessRequestedNotification::class);
    }

    public function test_requesting_twice_does_not_create_duplicate(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = $this->makeViticulturistForWinery($winery);

        NotebookAccessRequest::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::actingAs($winery)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->call('requestNotebookAccess')
            ->assertDispatched('toast');

        $this->assertSame(1, NotebookAccessRequest::where('winery_id', $winery->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->count());
    }

    public function test_cannot_request_if_already_has_access(): void
    {
        Notification::fake();

        $winery        = $this->makeWinery();
        $viticulturist = $this->makeViticulturistForWinery($winery);

        WineryViticulturist::where('winery_id', $winery->id)
            ->where('viticulturist_id', $viticulturist->id)
            ->update(['notebook_access' => true]);

        Livewire::actingAs($winery)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->call('requestNotebookAccess');

        $this->assertDatabaseMissing('notebook_access_requests', [
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
        ]);

        Notification::assertNothingSent();
    }

    // ── cancelar solicitud ────────────────────────────────────────────────────

    public function test_winery_can_cancel_pending_request(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = $this->makeViticulturistForWinery($winery);

        NotebookAccessRequest::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::actingAs($winery)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->call('cancelAccessRequest')
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('notebook_access_requests', [
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
        ]);
    }

    public function test_cancelling_nonexistent_request_is_safe(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = $this->makeViticulturistForWinery($winery);

        Livewire::actingAs($winery)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->call('cancelAccessRequest')
            ->assertDispatched('toast');
    }

    // ── estado visible tras aprobación ────────────────────────────────────────

    public function test_access_request_appears_in_render_after_creation(): void
    {
        $winery        = $this->makeWinery();
        $viticulturist = $this->makeViticulturistForWinery($winery);

        $request = NotebookAccessRequest::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        Livewire::actingAs($winery)
            ->test(Show::class, ['viticulturist' => $viticulturist])
            ->assertViewHas('accessRequest', fn($r) => $r?->id === $request->id);
    }

    public function test_access_request_is_null_for_ghost_viticulturist(): void
    {
        $winery = $this->makeWinery();

        $ghost = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => false,
        ]);

        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $ghost->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        Livewire::actingAs($winery)
            ->test(Show::class, ['viticulturist' => $ghost])
            ->assertViewHas('accessRequest', null);
    }
}
