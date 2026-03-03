<?php

namespace Tests\Feature\Viticulturist\CueExports;

use App\Livewire\Viticulturist\CueExports\Index;
use App\Models\CueExport;
use App\Models\Exploitation;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    private function makeExploitation(int $viticulturistId): Exploitation
    {
        return Exploitation::create([
            'viticulturist_id'         => $viticulturistId,
            'exploitation_name'        => 'Explotación Test',
            'holder_name'              => 'Test Holder',
            'holder_nif'               => '12345678A',
            'is_ecological'            => false,
            'is_integrated_production' => false,
            'is_quality_scheme'        => false,
            'active'                   => true,
        ]);
    }

    private function makeCueExport(int $viticulturistId, int $exploitationId, string $status = 'draft'): CueExport
    {
        return CueExport::create([
            'viticulturist_id' => $viticulturistId,
            'exploitation_id'  => $exploitationId,
            'campaign_year'    => 2024,
            'period_type'      => 'annual',
            'from_date'        => '2024-01-01',
            'to_date'          => '2024-12-31',
            'status'           => $status,
        ]);
    }

    public function test_index_shows_exports(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation  = $this->makeExploitation($viticulturist->id);
        $this->makeCueExport($viticulturist->id, $exploitation->id);

        $this->actingAs($viticulturist);

        // The index renders campaign_year in the table
        Livewire::test(Index::class)
            ->assertSee('2024');
    }

    public function test_mark_as_generated_transitions_draft(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation  = $this->makeExploitation($viticulturist->id);
        $export        = $this->makeCueExport($viticulturist->id, $exploitation->id, 'draft');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('markAsGenerated', $export->id);

        $this->assertDatabaseHas('cue_exports', [
            'id'     => $export->id,
            'status' => 'generated',
        ]);
    }

    public function test_cannot_mark_as_generated_if_already_generated(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation  = $this->makeExploitation($viticulturist->id);
        $export        = $this->makeCueExport($viticulturist->id, $exploitation->id, 'generated');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('markAsGenerated', $export->id);

        // Status remains 'generated', not changed to something else
        $this->assertDatabaseHas('cue_exports', [
            'id'     => $export->id,
            'status' => 'generated',
        ]);
    }

    public function test_mark_as_sent_transitions_generated(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation  = $this->makeExploitation($viticulturist->id);
        $export        = $this->makeCueExport($viticulturist->id, $exploitation->id, 'generated');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('markAsSent', $export->id);

        $this->assertDatabaseHas('cue_exports', [
            'id'     => $export->id,
            'status' => 'sent',
        ]);
    }

    public function test_delete_removes_draft(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation  = $this->makeExploitation($viticulturist->id);
        $export        = $this->makeCueExport($viticulturist->id, $exploitation->id, 'draft');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $export->id);

        $this->assertDatabaseMissing('cue_exports', ['id' => $export->id]);
    }

    public function test_cannot_delete_non_draft(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation  = $this->makeExploitation($viticulturist->id);
        $export        = $this->makeCueExport($viticulturist->id, $exploitation->id, 'generated');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $export->id);

        // Record is NOT deleted
        $this->assertDatabaseHas('cue_exports', ['id' => $export->id]);
    }
}
