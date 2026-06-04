<?php

namespace Tests\Feature\Supervisor\Inspection;

use App\Livewire\Supervisor\Inspection\Index as InspectionIndex;
use App\Models\DoInspection;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

/**
 * Tests for the Supervisor Inspection edit modal.
 *
 * Covers: open/close, field population, update, result field,
 * validation, and cross-supervisor authorization.
 */
class EditTest extends SupervisorTestCase
{
    // ── open / close ──────────────────────────────────────────────────────────

    public function test_open_edit_populates_fields(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = DoInspection::create([
            'supervisor_id' => $supervisor->id,
            'subject_type' => 'winery',
            'subject_id' => $winery->id,
            'inspection_date' => '2024-05-10',
            'notes' => 'Nota inicial',
            'reference_number' => 'INSP-001',
            'result' => DoInspection::RESULT_PENDING,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(InspectionIndex::class)
            ->call('openEdit', $inspection->id)
            ->assertSet('showEdit', true)
            ->assertSet('editInspectionId', $inspection->id)
            ->assertSet('editInspectionDate', '2024-05-10')
            ->assertSet('editNotes', 'Nota inicial')
            ->assertSet('editReferenceNumber', 'INSP-001')
            ->assertSet('editResult', DoInspection::RESULT_PENDING);
    }

    public function test_close_edit_hides_modal(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = DoInspection::create([
            'supervisor_id' => $supervisor->id,
            'subject_type' => 'winery',
            'subject_id' => $winery->id,
            'inspection_date' => now()->toDateString(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(InspectionIndex::class)
            ->call('openEdit', $inspection->id)
            ->assertSet('showEdit', true)
            ->call('closeEdit')
            ->assertSet('showEdit', false)
            ->assertSet('editInspectionId', null);
    }

    // ── update ────────────────────────────────────────────────────────────────

    public function test_can_update_inspection_details(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = DoInspection::create([
            'supervisor_id' => $supervisor->id,
            'subject_type' => 'winery',
            'subject_id' => $winery->id,
            'inspection_date' => '2024-01-01',
        ]);

        $this->actingAs($supervisor);

        Livewire::test(InspectionIndex::class)
            ->call('openEdit', $inspection->id)
            ->set('editInspectionDate', '2024-06-15')
            ->set('editResult', DoInspection::RESULT_COMPLIANT)
            ->set('editFindings', 'Todo correcto')
            ->set('editNotes', 'Revisión anual')
            ->set('editReferenceNumber', 'INSP-2024-42')
            ->call('updateInspection')
            ->assertSet('showEdit', false)
            ->assertHasNoErrors();

        $fresh = $inspection->fresh();
        $this->assertSame('2024-06-15', $fresh->inspection_date->format('Y-m-d'));
        $this->assertSame(DoInspection::RESULT_COMPLIANT, $fresh->result);
        $this->assertSame('Todo correcto', $fresh->findings);
        $this->assertSame('Revisión anual', $fresh->notes);
        $this->assertSame('INSP-2024-42', $fresh->reference_number);
    }

    public function test_can_mark_as_non_compliant_with_findings(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = DoInspection::create([
            'supervisor_id' => $supervisor->id,
            'subject_type' => 'winery',
            'subject_id' => $winery->id,
            'inspection_date' => now()->toDateString(),
            'status' => DoInspection::STATUS_COMPLETED,
        ]);

        $this->actingAs($supervisor);

        Livewire::test(InspectionIndex::class)
            ->call('openEdit', $inspection->id)
            ->set('editInspectionDate', now()->toDateString())
            ->set('editResult', DoInspection::RESULT_NON_COMPLIANT)
            ->set('editFindings', 'Incumplimiento de normativa de etiquetado')
            ->call('updateInspection')
            ->assertHasNoErrors();

        $this->assertSame(DoInspection::RESULT_NON_COMPLIANT, $inspection->fresh()->result);
        $this->assertSame('Incumplimiento de normativa de etiquetado', $inspection->fresh()->findings);
    }

    // ── validation ────────────────────────────────────────────────────────────

    public function test_update_requires_date(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = DoInspection::create([
            'supervisor_id' => $supervisor->id,
            'subject_type' => 'winery',
            'subject_id' => $winery->id,
            'inspection_date' => now()->toDateString(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(InspectionIndex::class)
            ->call('openEdit', $inspection->id)
            ->set('editInspectionDate', '')
            ->call('updateInspection')
            ->assertHasErrors(['editInspectionDate']);
    }

    public function test_update_rejects_invalid_result(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = DoInspection::create([
            'supervisor_id' => $supervisor->id,
            'subject_type' => 'winery',
            'subject_id' => $winery->id,
            'inspection_date' => now()->toDateString(),
        ]);

        $this->actingAs($supervisor);

        Livewire::test(InspectionIndex::class)
            ->call('openEdit', $inspection->id)
            ->set('editInspectionDate', now()->toDateString())
            ->set('editResult', 'invalido')
            ->call('updateInspection')
            ->assertHasErrors(['editResult']);
    }

    // ── authorization ─────────────────────────────────────────────────────────

    public function test_cannot_edit_another_supervisors_inspection(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $otherSupervisor = $this->makeSupervisor();

        $inspection = DoInspection::create([
            'supervisor_id' => $otherSupervisor->id,
            'subject_type' => 'winery',
            'subject_id' => $winery->id,
            'inspection_date' => now()->toDateString(),
        ]);

        $this->actingAs($supervisor);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(InspectionIndex::class)
            ->call('openEdit', $inspection->id);
    }
}
