<?php

namespace Tests\Feature\Winery\Denomination\Requests;

use App\Livewire\Winery\Denomination\Requests\Index;
use App\Models\SupervisorRequest;
use App\Models\SupervisorWinery;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class RequestsTest extends WineryTestCase
{
    private function makeSupervisor(): User
    {
        return User::factory()->create([
            'role'              => 'supervisor',
            'email_verified_at' => now(),
        ]);
    }

    private function makeLinkedSupervisor(User $winery): User
    {
        $supervisor = $this->makeSupervisor();

        SupervisorWinery::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'assigned_by'   => $supervisor->id,
        ]);

        return $supervisor;
    }

    // ── render ────────────────────────────────────────────────────────────────

    public function test_renders(): void
    {
        $winery = $this->makeWinery();

        $this->actingAs($winery)
            ->get(route('winery.denomination.requests.index'))
            ->assertOk();
    }

    public function test_shows_pending_requests(): void
    {
        $winery     = $this->makeWinery();
        $supervisor = $this->makeLinkedSupervisor($winery);

        SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'type'          => SupervisorRequest::TYPE_LABEL_REQUEST,
            'status'        => SupervisorRequest::STATUS_PENDING,
            'title'         => 'Solicitud etiquetas lote A',
            'sent_at'       => now(),
        ]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertSee('Solicitud etiquetas lote A');
    }

    // ── responder ─────────────────────────────────────────────────────────────

    public function test_winery_can_respond_to_pending_request(): void
    {
        $winery     = $this->makeWinery();
        $supervisor = $this->makeLinkedSupervisor($winery);

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'type'          => SupervisorRequest::TYPE_QUALIFICATION,
            'status'        => SupervisorRequest::STATUS_PENDING,
            'sent_at'       => now(),
        ]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->call('startResponding', $req->id)
            ->set('responseNotes', 'Adjunto documentación del lote.')
            ->call('respond')
            ->assertDispatched('toast');

        $this->assertEquals(SupervisorRequest::STATUS_IN_REVIEW, $req->fresh()->status);
        $this->assertEquals('Adjunto documentación del lote.', $req->fresh()->response_notes);
        $this->assertNotNull($req->fresh()->responded_at);
    }

    public function test_winery_cannot_respond_to_non_pending_request(): void
    {
        $winery     = $this->makeWinery();
        $supervisor = $this->makeLinkedSupervisor($winery);

        $req = SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $winery->id,
            'type'          => SupervisorRequest::TYPE_CERTIFICATION,
            'status'        => SupervisorRequest::STATUS_APPROVED, // ya resuelta
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->call('startResponding', $req->id)
            ->call('respond');
    }

    // ── aislamiento ──────────────────────────────────────────────────────────

    public function test_winery_only_sees_their_own_requests(): void
    {
        $winery      = $this->makeWinery();
        $otherWinery = $this->makeOtherWinery();
        $supervisor  = $this->makeSupervisor();

        SupervisorWinery::create(['supervisor_id' => $supervisor->id, 'winery_id' => $otherWinery->id, 'assigned_by' => $supervisor->id]);

        SupervisorRequest::create([
            'supervisor_id' => $supervisor->id,
            'winery_id'     => $otherWinery->id,
            'type'          => SupervisorRequest::TYPE_NONCONFORMITY,
            'title'         => 'Acta ajena',
            'status'        => SupervisorRequest::STATUS_PENDING,
        ]);

        Livewire::actingAs($winery)
            ->test(Index::class)
            ->assertDontSee('Acta ajena');
    }
}
