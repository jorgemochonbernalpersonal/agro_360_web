<?php

namespace Tests\Feature\Viticulturist\SoilAnalyses;

use App\Livewire\Viticulturist\SoilAnalyses\Edit;
use App\Models\Plot;
use App\Models\SoilAnalysis;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    private function makeAnalysis($viticulturist): SoilAnalysis
    {
        $plot = Plot::factory()->forViticulturist($viticulturist)->create();

        return SoilAnalysis::create([
            'viticulturist_id' => $viticulturist->id,
            'plot_id' => $plot->id,
            'analysis_date' => '2024-06-15',
            'laboratory' => 'Lab Original',
            'ph' => '6.5',
            'organic_matter' => '2.0',
        ]);
    }

    public function test_mount_fills_fields(): void
    {
        $v = $this->makeViticulturist();
        $analysis = $this->makeAnalysis($v);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['soilAnalysis' => $analysis])
            ->assertSet('laboratory', 'Lab Original')
            ->assertSet('analysis_date', '2024-06-15')
            ->assertSet('ph', '6.50');
    }

    public function test_can_update_soil_analysis(): void
    {
        $v = $this->makeViticulturist();
        $analysis = $this->makeAnalysis($v);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['soilAnalysis' => $analysis])
            ->set('laboratory', 'Nuevo Lab')
            ->set('ph', '7.2')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.soil-analyses.index'));

        $this->assertDatabaseHas('soil_analyses', [
            'id' => $analysis->id,
            'laboratory' => 'Nuevo Lab',
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $v = $this->makeViticulturist();
        $analysis = $this->makeAnalysis($v);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['soilAnalysis' => $analysis])
            ->set('plot_id', '')
            ->set('analysis_date', '')
            ->call('save')
            ->assertHasErrors(['plot_id', 'analysis_date']);
    }

    public function test_cannot_edit_other_viticulturists_analysis(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $analysis = $this->makeAnalysis($other);

        $this->actingAs($v)
            ->get(route('viticulturist.soil-analyses.edit', $analysis))
            ->assertStatus(403);
    }
}
