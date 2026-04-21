<?php

namespace Tests\Feature\Supervisor\Requests;

use App\Livewire\Supervisor\Requests\Index;
use App\Models\SupervisorRequest;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class BulkActionsTest extends SupervisorTestCase
{
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeRequest(int $supervisorId, int $wineryId, string $status): SupervisorRequest
    {
        return SupervisorRequest::create([
            'supervisor_id' => $supervisorId,
            'winery_id'     => $wineryId,
            'type'          => SupervisorRequest::TYPE_NONCONFORMITY,
            'status'        => $status,
        ]);
    }

    // ── bulkArchive ───────────────────────────────────────────────────────────

    public function test_bulk_archive_archives_approved_and_rejected_requests(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $approved = $this->makeRequest($supervisor->id, $winery->id, SupervisorRequest::STATUS_APPROVED);
        $rejected = $this->makeRequest($supervisor->id, $winery->id, SupervisorRequest::STATUS_REJECTED);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('selected', [$approved->id, $rejected->id])
            ->call('bulkArchive')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('supervisor_requests', ['id' => $approved->id, 'status' => SupervisorRequest::STATUS_ARCHIVED]);
        $this->assertDatabaseHas('supervisor_requests', ['id' => $rejected->id, 'status' => SupervisorRequest::STATUS_ARCHIVED]);
    }

    public function test_bulk_archive_skips_non_archivable_statuses(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $draft   = $this->makeRequest($supervisor->id, $winery->id, SupervisorRequest::STATUS_DRAFT);
        $pending = $this->makeRequest($supervisor->id, $winery->id, SupervisorRequest::STATUS_PENDING);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('selected', [$draft->id, $pending->id])
            ->call('bulkArchive');

        $this->assertDatabaseHas('supervisor_requests', ['id' => $draft->id,   'status' => SupervisorRequest::STATUS_DRAFT]);
        $this->assertDatabaseHas('supervisor_requests', ['id' => $pending->id, 'status' => SupervisorRequest::STATUS_PENDING]);
    }

    public function test_bulk_archive_clears_selection(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $req = $this->makeRequest($supervisor->id, $winery->id, SupervisorRequest::STATUS_APPROVED);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('selected', [$req->id])
            ->call('bulkArchive')
            ->assertSet('selected', []);
    }

    public function test_bulk_archive_with_empty_selection_does_nothing(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $req = $this->makeRequest($supervisor->id, $winery->id, SupervisorRequest::STATUS_APPROVED);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('selected', [])
            ->call('bulkArchive');

        $this->assertDatabaseHas('supervisor_requests', ['id' => $req->id, 'status' => SupervisorRequest::STATUS_APPROVED]);
    }

    public function test_bulk_archive_ignores_requests_from_another_supervisor(): void
    {
        [$supervisor, $winery]          = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $foreign = $this->makeRequest($otherSupervisor->id, $otherWinery->id, SupervisorRequest::STATUS_APPROVED);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('selected', [$foreign->id])
            ->call('bulkArchive');

        $this->assertDatabaseHas('supervisor_requests', ['id' => $foreign->id, 'status' => SupervisorRequest::STATUS_APPROVED]);
    }

    public function test_bulk_archive_mixed_statuses_only_archives_eligible(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $approved = $this->makeRequest($supervisor->id, $winery->id, SupervisorRequest::STATUS_APPROVED);
        $draft    = $this->makeRequest($supervisor->id, $winery->id, SupervisorRequest::STATUS_DRAFT);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('selected', [$approved->id, $draft->id])
            ->call('bulkArchive')
            ->assertDispatched('toast');

        $this->assertEquals(SupervisorRequest::STATUS_ARCHIVED, $approved->fresh()->status);
        $this->assertEquals(SupervisorRequest::STATUS_DRAFT,    $draft->fresh()->status);
    }

    // ── deleteDraft ───────────────────────────────────────────────────────────

    public function test_supervisor_can_delete_a_draft(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $draft = $this->makeRequest($supervisor->id, $winery->id, SupervisorRequest::STATUS_DRAFT);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('deleteDraft', $draft->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('supervisor_requests', ['id' => $draft->id]);
    }

    public function test_delete_draft_rejects_non_draft_status(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $pending = $this->makeRequest($supervisor->id, $winery->id, SupervisorRequest::STATUS_PENDING);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('deleteDraft', $pending->id);
    }

    public function test_delete_draft_rejects_another_supervisors_request(): void
    {
        [$supervisor]                    = $this->makeSupervisorWithWinery();
        [$otherSupervisor, $otherWinery] = $this->makeSupervisorWithWinery();

        $draft = $this->makeRequest($otherSupervisor->id, $otherWinery->id, SupervisorRequest::STATUS_DRAFT);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('deleteDraft', $draft->id);
    }

    public function test_cannot_delete_approved_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $approved = $this->makeRequest($supervisor->id, $winery->id, SupervisorRequest::STATUS_APPROVED);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('deleteDraft', $approved->id);
    }
}
