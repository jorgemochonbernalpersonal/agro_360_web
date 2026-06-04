<?php

namespace Tests\Feature\Supervisor\Requests;

use App\Livewire\Supervisor\Requests\Index;
use App\Models\SupervisorRequest;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class RequestsTest extends SupervisorTestCase
{
    // ── render ────────────────────────────────────────────────────────────────

    public function test_renders(): void
    {
        $supervisor = $this->makeSupervisor();

        $this->actingAs($supervisor)
            ->get(route('supervisor.requests.index'))
            ->assertOk();
    }

    // ── crear ─────────────────────────────────────────────────────────────────

    public function test_supervisor_can_create_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('formWineryId', $winery->id)
            ->set('formType', SupervisorRequest::TYPE_QUALIFICATION)
            ->set('formTitle', 'Lote R-2026-001')
            ->call('create')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('supervisor_requests', [
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_QUALIFICATION,
            'status' => SupervisorRequest::STATUS_DRAFT,
            'title' => 'Lote R-2026-001',
        ]);
    }

    public function test_create_validates_required_fields(): void
    {
        [$supervisor] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('create')
            ->assertHasErrors(['formWineryId', 'formType']);
    }

    public function test_cannot_create_request_for_unassigned_winery(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery(); // no adscrita

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('formWineryId', $winery->id)
            ->set('formType', SupervisorRequest::TYPE_NONCONFORMITY)
            ->call('create');
    }

    // ── flujo de estados ──────────────────────────────────────────────────────

    public function test_supervisor_can_send_draft_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_LABEL_REQUEST,
            'status' => SupervisorRequest::STATUS_DRAFT,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('send', $req->id)
            ->assertDispatched('toast');

        $this->assertEquals(SupervisorRequest::STATUS_PENDING, $req->fresh()->status);
        $this->assertNotNull($req->fresh()->sent_at);
    }

    public function test_supervisor_can_approve_in_review_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_QUALIFICATION,
            'status' => SupervisorRequest::STATUS_IN_REVIEW,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('approve', $req->id)
            ->assertDispatched('toast');

        $this->assertEquals(SupervisorRequest::STATUS_APPROVED, $req->fresh()->status);
    }

    public function test_supervisor_can_reject_in_review_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_HARVEST_DECLARATION,
            'status' => SupervisorRequest::STATUS_IN_REVIEW,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('reject', $req->id)
            ->assertDispatched('toast');

        $this->assertEquals(SupervisorRequest::STATUS_REJECTED, $req->fresh()->status);
    }

    public function test_supervisor_cannot_touch_another_supervisors_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor = $this->makeSupervisor();

        $req = SupervisorRequest::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_CERTIFICATION,
            'status' => SupervisorRequest::STATUS_DRAFT,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('send', $req->id);
    }
}
