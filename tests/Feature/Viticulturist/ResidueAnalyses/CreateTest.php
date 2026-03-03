<?php

namespace Tests\Feature\Viticulturist\ResidueAnalyses;

use App\Livewire\Viticulturist\ResidueAnalyses\Create;
use App\Models\Campaign;
use App\Models\ResidueAnalysis;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_analysis(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        // mount() auto-creates campaign and sets campaign_id + analysis_date
        Livewire::test(Create::class)
            ->set('laboratory_name', 'Laboratorio Central Test')
            ->set('analysis_date', '2024-06-15')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.residue-analyses.index'));

        $this->assertDatabaseHas('residue_analyses', [
            'viticulturist_id' => $viticulturist->id,
            'laboratory_name'  => 'Laboratorio Central Test',
            'active'           => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('campaign_id', '')
            ->set('laboratory_name', '')
            ->set('analysis_date', '')
            ->call('save')
            ->assertHasErrors(['campaign_id', 'laboratory_name', 'analysis_date']);
    }

    public function test_compliant_flag_defaults_to_true(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('laboratory_name', 'Lab Default Test')
            ->set('analysis_date', '2024-06-15')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('residue_analyses', [
            'viticulturist_id'  => $viticulturist->id,
            'laboratory_name'   => 'Lab Default Test',
            'overall_compliant' => true,
        ]);
    }

    public function test_non_compliant_can_be_saved(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('laboratory_name', 'Lab Non Compliant')
            ->set('analysis_date', '2024-06-15')
            ->set('overall_compliant', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('residue_analyses', [
            'viticulturist_id'  => $viticulturist->id,
            'laboratory_name'   => 'Lab Non Compliant',
            'overall_compliant' => false,
        ]);
    }
}
