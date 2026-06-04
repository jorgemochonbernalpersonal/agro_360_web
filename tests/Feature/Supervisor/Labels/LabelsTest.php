<?php

namespace Tests\Feature\Supervisor\Labels;

use App\Livewire\Supervisor\Labels\Index;
use App\Models\DoLabel;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class LabelsTest extends SupervisorTestCase
{
    // ── crear solicitud ───────────────────────────────────────────────────

    public function test_supervisor_can_create_label_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('winery_id', (string) $winery->id)
            ->set('vintage', (string) now()->year)
            ->set('batch_number', 'LOTE-001')
            ->set('quantity_requested', 500)
            ->call('saveLabel')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_labels', [
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'batch_number' => 'LOTE-001',
            'quantity_requested' => 500,
            'status' => DoLabel::STATUS_PENDING,
        ]);
    }

    public function test_supervisor_cannot_create_label_for_unlinked_winery(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery = $this->makeWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('winery_id', (string) $winery->id)
            ->set('vintage', (string) now()->year)
            ->set('quantity_requested', 100)
            ->call('saveLabel');

        $this->assertDatabaseMissing('do_labels', [
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
        ]);
    }

    // ── aprobar ───────────────────────────────────────────────────────────

    public function test_supervisor_can_approve_label_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $label = DoLabel::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'vintage' => now()->year,
            'quantity_requested' => 200,
            'status' => DoLabel::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('approve', $label->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_labels', [
            'id' => $label->id,
            'status' => DoLabel::STATUS_APPROVED,
        ]);
    }

    // ── emitir ────────────────────────────────────────────────────────────

    public function test_supervisor_can_issue_labels(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $label = DoLabel::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'vintage' => now()->year,
            'quantity_requested' => 300,
            'status' => DoLabel::STATUS_APPROVED,
            'requested_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('issue', $label->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_labels', [
            'id' => $label->id,
            'status' => DoLabel::STATUS_ISSUED,
            'quantity_issued' => 300,
            'quantity_stock' => 300,
        ]);
    }

    // ── cancelar ──────────────────────────────────────────────────────────

    public function test_supervisor_can_cancel_label_request(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $label = DoLabel::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'vintage' => now()->year,
            'quantity_requested' => 100,
            'status' => DoLabel::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('cancel', $label->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_labels', [
            'id' => $label->id,
            'status' => DoLabel::STATUS_CANCELLED,
        ]);
    }

    // ── guard: otro supervisor no puede modificar ─────────────────────────

    public function test_another_supervisor_cannot_modify_labels(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor = $this->makeSupervisor();

        $label = DoLabel::create([
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'vintage' => now()->year,
            'quantity_requested' => 100,
            'status' => DoLabel::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($otherSupervisor)
            ->test(Index::class)
            ->call('cancel', $label->id);
    }
}
