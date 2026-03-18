<?php

namespace Tests\Feature\Supervisor\Inspection;

use App\Livewire\Supervisor\Inspection\Index;
use App\Models\DoInspection;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class InspectionTest extends SupervisorTestCase
{
    // ── programar inspección ──────────────────────────────────────────────

    public function test_supervisor_can_schedule_inspection_for_winery(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('subject_type', 'winery')
            ->set('subject_id', (string) $winery->id)
            ->set('inspection_date', now()->format('Y-m-d'))
            ->set('reference_number', 'INSP-2026-001')
            ->call('saveInspection')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_inspections', [
            'supervisor_id'    => $supervisor->id,
            'subject_type'     => 'winery',
            'subject_id'       => $winery->id,
            'reference_number' => 'INSP-2026-001',
            'status'           => DoInspection::STATUS_SCHEDULED,
        ]);
    }

    public function test_supervisor_cannot_schedule_inspection_for_unlinked_winery(): void
    {
        $supervisor = $this->makeSupervisor();
        $winery     = $this->makeWinery(); // no SupervisorWinery link

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->set('subject_type', 'winery')
            ->set('subject_id', (string) $winery->id)
            ->set('inspection_date', now()->format('Y-m-d'))
            ->call('saveInspection');

        $this->assertDatabaseMissing('do_inspections', [
            'supervisor_id' => $supervisor->id,
            'subject_id'    => $winery->id,
        ]);
    }

    // ── cambio de estado ──────────────────────────────────────────────────

    public function test_supervisor_can_update_inspection_status(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = DoInspection::create([
            'supervisor_id'   => $supervisor->id,
            'subject_type'    => 'winery',
            'subject_id'      => $winery->id,
            'inspection_date' => now()->format('Y-m-d'),
            'status'          => DoInspection::STATUS_SCHEDULED,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('updateStatus', $inspection->id, DoInspection::STATUS_IN_PROGRESS)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_inspections', [
            'id'     => $inspection->id,
            'status' => DoInspection::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_supervisor_can_complete_inspection(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = DoInspection::create([
            'supervisor_id'   => $supervisor->id,
            'subject_type'    => 'winery',
            'subject_id'      => $winery->id,
            'inspection_date' => now()->format('Y-m-d'),
            'status'          => DoInspection::STATUS_IN_PROGRESS,
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('updateStatus', $inspection->id, DoInspection::STATUS_COMPLETED)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('do_inspections', [
            'id'     => $inspection->id,
            'status' => DoInspection::STATUS_COMPLETED,
        ]);
    }

    // ── eliminar ──────────────────────────────────────────────────────────

    public function test_supervisor_can_delete_inspection(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = DoInspection::create([
            'supervisor_id'   => $supervisor->id,
            'subject_type'    => 'winery',
            'subject_id'      => $winery->id,
            'inspection_date' => now()->format('Y-m-d'),
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('delete', $inspection->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('do_inspections', ['id' => $inspection->id]);
    }

    // ── guard: otro supervisor no puede modificar ─────────────────────────

    public function test_another_supervisor_cannot_modify_inspection(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor       = $this->makeSupervisor();

        $inspection = DoInspection::create([
            'supervisor_id'   => $supervisor->id,
            'subject_type'    => 'winery',
            'subject_id'      => $winery->id,
            'inspection_date' => now()->format('Y-m-d'),
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($otherSupervisor)
            ->test(Index::class)
            ->call('delete', $inspection->id);
    }
}
