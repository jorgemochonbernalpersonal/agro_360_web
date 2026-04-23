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

/**
 * Cubre casos borde del flujo de solicitud de acceso al cuaderno
 * que no están en NotebookAccessTest: re-solicitud tras rechazo,
 * viticultor ghost (can_login=false) y concurrencia de solicitudes.
 */
class NotebookAccessEdgeCasesTest extends WineryTestCase
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
            'can_login' => true,
        ]);

        $this->relation = WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        $this->actingAs($this->winery);
    }

    // ── Re-solicitud tras rechazo ─────────────────────────────────────────────

    public function test_re_request_after_rejection_creates_new_pending_record(): void
    {
        // Primera solicitud ya rechazada
        NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_REJECTED,
            'requested_at'     => now()->subDays(5),
            'responded_at'     => now()->subDays(3),
        ]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('requestNotebookAccess')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notebook_access_requests', [
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
        ]);
    }

    public function test_re_request_after_rejection_sends_notification(): void
    {
        Notification::fake();

        NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_REJECTED,
            'requested_at'     => now()->subDays(5),
            'responded_at'     => now()->subDays(3),
        ]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('requestNotebookAccess');

        Notification::assertSentTo($this->viticulturist, NotebookAccessRequestedNotification::class);
    }

    public function test_re_request_does_not_duplicate_records(): void
    {
        NotebookAccessRequest::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_REJECTED,
            'requested_at'     => now()->subDays(5),
            'responded_at'     => now()->subDays(3),
        ]);

        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('requestNotebookAccess');

        // updateOrCreate must reuse the existing row — not insert a second one
        $this->assertDatabaseCount('notebook_access_requests', 1);
    }

    // ── Ghost viticulturist (can_login=false) ─────────────────────────────────

    public function test_show_renders_without_error_for_ghost_viticulturist(): void
    {
        $ghost = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => false,
            'email'     => 'viticultores.' . uniqid() . '@placeholder.agro365.es',
        ]);

        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $ghost->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        // The show component must render without exceptions even for ghost viticultors
        Livewire::test(Show::class, ['viticulturist' => $ghost])
            ->assertOk();
    }

    // ── Solicitud pendiente de otra bodega no interfiere ──────────────────────

    public function test_pending_request_from_other_winery_does_not_block_this_winery(): void
    {
        $otherWinery = $this->makeOtherWinery();

        WineryViticulturist::create([
            'winery_id'        => $otherWinery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $otherWinery->id,
        ]);

        // Another winery has a pending request to the same viticulturist
        NotebookAccessRequest::create([
            'winery_id'        => $otherWinery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
            'requested_at'     => now(),
        ]);

        // This winery should still be able to request (different winery_id scope)
        Livewire::test(Show::class, ['viticulturist' => $this->viticulturist])
            ->call('requestNotebookAccess')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notebook_access_requests', [
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'status'           => NotebookAccessRequest::STATUS_PENDING,
        ]);
    }

    // ── Múltiples viticultores de la misma bodega ─────────────────────────────

    public function test_notebook_access_state_is_per_viticulturist(): void
    {
        $secondViticulturist = User::factory()->create([
            'role'      => 'viticulturist',
            'can_login' => true,
        ]);

        WineryViticulturist::create([
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $secondViticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $this->winery->id,
        ]);

        // Grant access only to the first viticulturist
        $this->relation->grantNotebookAccess();

        // Second viticulturist should have NO access
        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $secondViticulturist->id,
            'notebook_access'  => false,
        ]);

        // First viticulturist should still have access
        $this->assertDatabaseHas('winery_viticulturist', [
            'winery_id'        => $this->winery->id,
            'viticulturist_id' => $this->viticulturist->id,
            'notebook_access'  => true,
        ]);
    }
}
