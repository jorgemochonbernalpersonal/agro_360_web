<?php

namespace Tests\Feature\Viticulturist\ResidueAnalyses;

use App\Livewire\Viticulturist\ResidueAnalyses\Edit;
use App\Models\Campaign;
use App\Models\ResidueAnalysis;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $analysis = $this->makeAnalysis($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['residueAnalysis' => $analysis])
            ->assertSet('laboratory_name', 'Lab Original')
            ->assertSet('analysis_date', '2024-06-15')
            ->assertSet('overall_compliant', true);
    }

    public function test_can_update_analysis(): void
    {
        $viticulturist = $this->makeViticulturist();
        $analysis = $this->makeAnalysis($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['residueAnalysis' => $analysis])
            ->set('laboratory_name', 'Lab Actualizado')
            ->set('analysis_date', '2024-09-01')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.residue-analyses.index'));

        $this->assertDatabaseHas('residue_analyses', [
            'id' => $analysis->id,
            'laboratory_name' => 'Lab Actualizado',
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $analysis = $this->makeAnalysis($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['residueAnalysis' => $analysis])
            ->set('laboratory_name', '')
            ->set('analysis_date', '')
            ->call('save')
            ->assertHasErrors(['laboratory_name', 'analysis_date']);
    }

    public function test_overall_compliant_can_be_toggled(): void
    {
        $viticulturist = $this->makeViticulturist();
        $analysis = $this->makeAnalysis($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['residueAnalysis' => $analysis])
            ->set('overall_compliant', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('residue_analyses', [
            'id' => $analysis->id,
            'overall_compliant' => false,
        ]);
    }

    public function test_cannot_edit_other_viticulturists_analysis(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $analysis = $this->makeAnalysis($viticulturist->id);

        $this->actingAs($other)
            ->get(route('viticulturist.residue-analyses.edit', $analysis))
            ->assertStatus(403);
    }

    private function makeAnalysis(int $viticulturistId): ResidueAnalysis
    {
        $campaign = Campaign::create([
            'viticulturist_id' => $viticulturistId,
            'year' => 2024,
            'name' => 'Campaña 2024',
        ]);

        return ResidueAnalysis::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id' => $campaign->id,
            'analysis_date' => '2024-06-15',
            'laboratory_name' => 'Lab Original',
            'overall_compliant' => true,
            'active' => true,
        ]);
    }
}
