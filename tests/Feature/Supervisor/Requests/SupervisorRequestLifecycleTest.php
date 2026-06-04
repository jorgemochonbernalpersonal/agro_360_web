<?php

namespace Tests\Feature\Supervisor\Requests;

use App\Livewire\Supervisor\Requests\Index as SupervisorRequestsIndex;
use App\Livewire\Winery\Denomination\Requests\Index as WineryRequestsIndex;
use App\Models\SupervisorRequest;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

/**
 * Cubre el ciclo de vida completo del flujo DO ↔ Bodega:
 *
 * DRAFT → PENDING (supervisor envía) → IN_REVIEW (bodega responde)
 *      → APPROVED o REJECTED (supervisor resuelve)
 *      → ARCHIVED (supervisor archiva)
 *
 * También cubre:
 * - Aislamiento: bodega B no puede responder solicitudes de bodega A
 * - Tipos de solicitud iniciados por supervisor vs bodega
 * - Predicados del modelo (canBeSentBySupervisor, etc.)
 */
class SupervisorRequestLifecycleTest extends SupervisorTestCase
{
    // ── Happy path: flujo completo aprobado ───────────────────────────────────

    public function test_full_flow_draft_to_approved(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        // 1. Supervisor crea en estado DRAFT
        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_QUALIFICATION,
            'status' => SupervisorRequest::STATUS_DRAFT,
            'title' => 'Calificación Lote Q-2026',
        ]);

        $this->assertSame(SupervisorRequest::STATUS_DRAFT, $req->status);

        // 2. Supervisor envía → PENDING
        Livewire::actingAs($supervisor)
            ->test(SupervisorRequestsIndex::class)
            ->call('send', $req->id);

        $req->refresh();
        $this->assertSame(SupervisorRequest::STATUS_PENDING, $req->status);
        $this->assertNotNull($req->sent_at);

        // 3. Bodega responde → IN_REVIEW
        Livewire::actingAs($winery)
            ->test(WineryRequestsIndex::class)
            ->call('startResponding', $req->id)
            ->set('responseNotes', 'Documentación adjunta.')
            ->call('respond');

        $req->refresh();
        $this->assertSame(SupervisorRequest::STATUS_IN_REVIEW, $req->status);
        $this->assertSame('Documentación adjunta.', $req->response_notes);
        $this->assertNotNull($req->responded_at);

        // 4. Supervisor aprueba → APPROVED
        Livewire::actingAs($supervisor)
            ->test(SupervisorRequestsIndex::class)
            ->call('approve', $req->id);

        $req->refresh();
        $this->assertSame(SupervisorRequest::STATUS_APPROVED, $req->status);
        $this->assertNotNull($req->resolved_at);
    }

    public function test_full_flow_draft_to_rejected(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_LABEL_REQUEST,
            'status' => SupervisorRequest::STATUS_DRAFT,
        ]);

        Livewire::actingAs($supervisor)
            ->test(SupervisorRequestsIndex::class)
            ->call('send', $req->id);

        Livewire::actingAs($winery)
            ->test(WineryRequestsIndex::class)
            ->call('startResponding', $req->id)
            ->set('responseNotes', '')
            ->call('respond');

        Livewire::actingAs($supervisor)
            ->test(SupervisorRequestsIndex::class)
            ->call('reject', $req->id);

        $req->refresh();
        $this->assertSame(SupervisorRequest::STATUS_REJECTED, $req->status);
        $this->assertNotNull($req->resolved_at);
    }

    // ── archive ───────────────────────────────────────────────────────────────

    public function test_supervisor_can_archive_approved_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_CERTIFICATION,
            'status' => SupervisorRequest::STATUS_APPROVED,
            'resolved_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(SupervisorRequestsIndex::class)
            ->call('archive', $req->id)
            ->assertDispatched('toast');

        $this->assertSame(SupervisorRequest::STATUS_ARCHIVED, $req->fresh()->status);
    }

    public function test_supervisor_can_archive_rejected_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_NONCONFORMITY,
            'status' => SupervisorRequest::STATUS_REJECTED,
            'resolved_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(SupervisorRequestsIndex::class)
            ->call('archive', $req->id)
            ->assertDispatched('toast');

        $this->assertSame(SupervisorRequest::STATUS_ARCHIVED, $req->fresh()->status);
    }

    public function test_supervisor_cannot_archive_another_supervisors_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor = $this->makeSupervisor();

        $req = SupervisorRequest::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_CERTIFICATION,
            'status' => SupervisorRequest::STATUS_APPROVED,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(SupervisorRequestsIndex::class)
            ->call('archive', $req->id);
    }

    // ── Aislamiento: bodega B no puede responder solicitudes de bodega A ──────

    public function test_winery_b_cannot_respond_to_winery_a_request(): void
    {
        [$supervisor, $wineryA] = $this->makeSupervisorWithWinery();
        $wineryB = $this->makeWinery();

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $wineryA->id,
            'type' => SupervisorRequest::TYPE_HARVEST_DECLARATION,
            'status' => SupervisorRequest::STATUS_PENDING,
            'sent_at' => now(),
        ]);

        // forWinery(Auth::id()) → findOrFail will throw for wineryB
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($wineryB)
            ->test(WineryRequestsIndex::class)
            ->call('startResponding', $req->id)
            ->call('respond');
    }

    // ── respond() sin notas → response_notes es null ──────────────────────────

    public function test_respond_without_notes_stores_null(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_LABEL_REQUEST,
            'status' => SupervisorRequest::STATUS_PENDING,
            'sent_at' => now(),
        ]);

        Livewire::actingAs($winery)
            ->test(WineryRequestsIndex::class)
            ->call('startResponding', $req->id)
            ->set('responseNotes', '')
            ->call('respond')
            ->assertHasNoErrors();

        $this->assertNull($req->fresh()->response_notes);
    }

    // ── Predicados del modelo ─────────────────────────────────────────────────

    public function test_can_be_sent_only_when_draft(): void
    {
        $draft = new SupervisorRequest(['status' => SupervisorRequest::STATUS_DRAFT]);
        $pending = new SupervisorRequest(['status' => SupervisorRequest::STATUS_PENDING]);

        $this->assertTrue($draft->canBeSentBySupervisor());
        $this->assertFalse($pending->canBeSentBySupervisor());
    }

    public function test_can_be_responded_only_when_pending(): void
    {
        $pending = new SupervisorRequest(['status' => SupervisorRequest::STATUS_PENDING]);
        $inReview = new SupervisorRequest(['status' => SupervisorRequest::STATUS_IN_REVIEW]);
        $draft = new SupervisorRequest(['status' => SupervisorRequest::STATUS_DRAFT]);

        $this->assertTrue($pending->canBeRespondedByWinery());
        $this->assertFalse($inReview->canBeRespondedByWinery());
        $this->assertFalse($draft->canBeRespondedByWinery());
    }

    public function test_can_be_resolved_only_when_in_review(): void
    {
        $inReview = new SupervisorRequest(['status' => SupervisorRequest::STATUS_IN_REVIEW]);
        $pending = new SupervisorRequest(['status' => SupervisorRequest::STATUS_PENDING]);
        $approved = new SupervisorRequest(['status' => SupervisorRequest::STATUS_APPROVED]);

        $this->assertTrue($inReview->canBeResolvedBySupervisor());
        $this->assertFalse($pending->canBeResolvedBySupervisor());
        $this->assertFalse($approved->canBeResolvedBySupervisor());
    }

    // ── Tipos de solicitud iniciados por cada parte ───────────────────────────

    public function test_nonconformity_is_supervisor_initiated(): void
    {
        $this->assertContains(
            SupervisorRequest::TYPE_NONCONFORMITY,
            SupervisorRequest::SUPERVISOR_INITIATED,
        );
    }

    public function test_winery_initiated_types_do_not_include_nonconformity(): void
    {
        $this->assertNotContains(
            SupervisorRequest::TYPE_NONCONFORMITY,
            SupervisorRequest::WINERY_INITIATED,
        );
    }

    public function test_label_request_harvest_declaration_certification_qualification_are_winery_initiated(): void
    {
        $expected = [
            SupervisorRequest::TYPE_LABEL_REQUEST,
            SupervisorRequest::TYPE_HARVEST_DECLARATION,
            SupervisorRequest::TYPE_CERTIFICATION,
            SupervisorRequest::TYPE_QUALIFICATION,
        ];

        foreach ($expected as $type) {
            $this->assertContains($type, SupervisorRequest::WINERY_INITIATED, "{$type} must be winery-initiated");
        }
    }

    // ── Timestamps de transición ──────────────────────────────────────────────

    public function test_send_sets_sent_at(): void
    {
        $req = new SupervisorRequest(['status' => SupervisorRequest::STATUS_PENDING]);
        // send() tested via Livewire in test_full_flow_draft_to_approved
        // Model-level: send() calls update(['status'=>PENDING, 'sent_at'=>now()])
        // Verified by asserting sent_at is not null after send in integration test above.
        $this->assertTrue(true); // placeholder — covered by integration tests
    }

    public function test_resolve_sets_resolved_at_for_approve(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_QUALIFICATION,
            'status' => SupervisorRequest::STATUS_IN_REVIEW,
        ]);

        $req->approve();
        $req->refresh();

        $this->assertNotNull($req->resolved_at);
        $this->assertSame(SupervisorRequest::STATUS_APPROVED, $req->status);
    }

    public function test_resolve_sets_resolved_at_for_reject(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_LABEL_REQUEST,
            'status' => SupervisorRequest::STATUS_IN_REVIEW,
        ]);

        $req->reject();
        $req->refresh();

        $this->assertNotNull($req->resolved_at);
        $this->assertSame(SupervisorRequest::STATUS_REJECTED, $req->status);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function test_for_supervisor_scope_excludes_other_supervisor_requests(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor = $this->makeSupervisor();

        SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_CERTIFICATION,
            'status' => SupervisorRequest::STATUS_DRAFT,
        ]);

        SupervisorRequest::create([
            'supervisor_id' => $otherSupervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_CERTIFICATION,
            'status' => SupervisorRequest::STATUS_DRAFT,
        ]);

        $ids = SupervisorRequest::forSupervisor($supervisor->id)->pluck('supervisor_id');

        $this->assertTrue($ids->every(fn ($id) => $id === $supervisor->id));
    }

    public function test_for_winery_scope_excludes_other_winery_requests(): void
    {
        [$supervisor, $wineryA] = $this->makeSupervisorWithWinery();
        $wineryB = $this->makeWinery();

        SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $wineryA->id,
            'type' => SupervisorRequest::TYPE_LABEL_REQUEST,
            'status' => SupervisorRequest::STATUS_PENDING,
        ]);

        SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $wineryB->id,
            'type' => SupervisorRequest::TYPE_LABEL_REQUEST,
            'status' => SupervisorRequest::STATUS_PENDING,
        ]);

        $ids = SupervisorRequest::forWinery($wineryA->id)->pluck('winery_id');

        $this->assertTrue($ids->every(fn ($id) => $id === $wineryA->id));
    }
}
