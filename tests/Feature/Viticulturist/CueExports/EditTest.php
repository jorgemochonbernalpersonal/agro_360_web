<?php

namespace Tests\Feature\Viticulturist\CueExports;

use App\Livewire\Viticulturist\CueExports\Edit;
use App\Models\CueExport;
use App\Models\Exploitation;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_fields_for_draft(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation = $this->makeExploitation($viticulturist->id);
        $export = $this->makeCueExport($viticulturist->id, $exploitation->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['cueExport' => $export])
            ->assertSet('exploitation_id', (string) $exploitation->id)
            ->assertSet('campaign_year', 2024)
            ->assertSet('from_date', '2024-01-01')
            ->assertSet('to_date', '2024-12-31');
    }

    public function test_can_update_draft_cue_export(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation = $this->makeExploitation($viticulturist->id);
        $export = $this->makeCueExport($viticulturist->id, $exploitation->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['cueExport' => $export])
            ->set('campaign_year', 2023)
            ->set('from_date', '2023-01-01')
            ->set('to_date', '2023-12-31')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.cue-exports.index'));

        $this->assertDatabaseHas('cue_exports', [
            'id' => $export->id,
            'campaign_year' => 2023,
        ]);
    }

    public function test_non_draft_redirects_on_mount(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation = $this->makeExploitation($viticulturist->id);
        $export = $this->makeCueExport($viticulturist->id, $exploitation->id, 'generated');

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['cueExport' => $export])
            ->assertRedirect(route('viticulturist.cue-exports.index'));
    }

    public function test_validates_required_fields_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation = $this->makeExploitation($viticulturist->id);
        $export = $this->makeCueExport($viticulturist->id, $exploitation->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['cueExport' => $export])
            ->set('exploitation_id', '')
            ->set('from_date', '')
            ->call('save')
            ->assertHasErrors(['exploitation_id', 'from_date']);
    }

    public function test_to_date_must_be_after_from_date_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $exploitation = $this->makeExploitation($viticulturist->id);
        $export = $this->makeCueExport($viticulturist->id, $exploitation->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['cueExport' => $export])
            ->set('from_date', '2024-06-01')
            ->set('to_date', '2024-05-01')
            ->call('save')
            ->assertHasErrors(['to_date']);
    }

    public function test_cannot_edit_other_viticulturists_export(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $exploitation = $this->makeExploitation($viticulturist->id);
        $export = $this->makeCueExport($viticulturist->id, $exploitation->id);

        $this->actingAs($other)
            ->get(route('viticulturist.cue-exports.edit', $export))
            ->assertStatus(403);
    }

    private function makeExploitation(int $viticulturistId): Exploitation
    {
        return Exploitation::create([
            'viticulturist_id' => $viticulturistId,
            'exploitation_name' => 'Explotación Test',
            'holder_name' => 'Test Holder',
            'holder_nif' => '12345678A',
            'is_ecological' => false,
            'is_integrated_production' => false,
            'is_quality_scheme' => false,
            'active' => true,
        ]);
    }

    private function makeCueExport(int $viticulturistId, int $exploitationId, string $status = 'draft'): CueExport
    {
        return CueExport::create([
            'viticulturist_id' => $viticulturistId,
            'exploitation_id' => $exploitationId,
            'campaign_year' => 2024,
            'period_type' => 'annual',
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'status' => $status,
        ]);
    }
}
