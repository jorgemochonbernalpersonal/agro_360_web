<?php

namespace Tests\Feature\Supervisor\Inspection;

use App\Livewire\Supervisor\Inspection\Index;
use App\Models\DoInspection;
use App\Models\SupervisorRequest;
use Livewire\Livewire;
use Tests\Feature\SupervisorTestCase;

class NonconformityTest extends SupervisorTestCase
{
    // ── happy path ────────────────────────────────────────────────────────────

    public function test_creates_nonconformity_request_from_non_compliant_inspection(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = $this->makeInspection($supervisor->id, $winery->id);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('createNonconformityFromInspection', $inspection->id);

        $this->assertDatabaseHas('supervisor_requests', [
            'supervisor_id' => $supervisor->id,
            'winery_id' => $winery->id,
            'type' => SupervisorRequest::TYPE_NONCONFORMITY,
            'status' => SupervisorRequest::STATUS_DRAFT,
        ]);
    }

    public function test_title_uses_reference_number_when_present(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = $this->makeInspection($supervisor->id, $winery->id, [
            'reference_number' => 'REF-2026-001',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('createNonconformityFromInspection', $inspection->id);

        $request = SupervisorRequest::where('supervisor_id', $supervisor->id)->first();
        $this->assertStringContainsString('REF-2026-001', $request->title);
    }

    public function test_title_uses_date_when_no_reference_number(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = $this->makeInspection($supervisor->id, $winery->id, [
            'inspection_date' => '2026-03-15',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('createNonconformityFromInspection', $inspection->id);

        $request = SupervisorRequest::where('supervisor_id', $supervisor->id)->first();
        $this->assertStringContainsString('15/03/2026', $request->title);
    }

    public function test_notes_copied_from_inspection_findings(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = $this->makeInspection($supervisor->id, $winery->id, [
            'findings' => 'Se detectaron irregularidades en el etiquetado.',
        ]);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('createNonconformityFromInspection', $inspection->id);

        $request = SupervisorRequest::where('supervisor_id', $supervisor->id)->first();
        $this->assertEquals('Se detectaron irregularidades en el etiquetado.', $request->notes);
    }

    // ── guards ────────────────────────────────────────────────────────────────

    public function test_cannot_create_from_compliant_inspection(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();

        $inspection = $this->makeInspection($supervisor->id, $winery->id, [
            'result' => DoInspection::RESULT_COMPLIANT,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('createNonconformityFromInspection', $inspection->id);
    }

    public function test_cannot_create_from_viticulturist_inspection(): void
    {
        $supervisor = $this->makeSupervisor();
        $viticulturist = $this->makeViticulturistForSupervisor($supervisor);

        $inspection = DoInspection::create([
            'supervisor_id' => $supervisor->id,
            'subject_type' => 'viticulturist',
            'subject_id' => $viticulturist->id,
            'inspection_date' => now()->format('Y-m-d'),
            'result' => DoInspection::RESULT_NON_COMPLIANT,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($supervisor)
            ->test(Index::class)
            ->call('createNonconformityFromInspection', $inspection->id);
    }

    public function test_another_supervisor_cannot_use_inspection(): void
    {
        [$supervisor, $winery] = $this->makeSupervisorWithWinery();
        $other = $this->makeSupervisor();

        $inspection = $this->makeInspection($supervisor->id, $winery->id);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($other)
            ->test(Index::class)
            ->call('createNonconformityFromInspection', $inspection->id);
    }
    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeInspection(int $supervisorId, int $subjectId, array $attrs = []): DoInspection
    {
        return DoInspection::create(array_merge([
            'supervisor_id' => $supervisorId,
            'subject_type' => 'winery',
            'subject_id' => $subjectId,
            'inspection_date' => now()->format('Y-m-d'),
            'result' => DoInspection::RESULT_NON_COMPLIANT,
        ], $attrs));
    }
}
