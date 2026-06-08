<?php

namespace Tests\Feature\Viticulturist\SoilAnalyses;

use App\Livewire\Viticulturist\SoilAnalyses\Create;
use App\Models\Plot;
use App\Models\SoilAnalysis;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_soil_analysis(): void
    {
        $v = $this->makeViticulturist();
        $plot = Plot::factory()->forViticulturist($v)->create();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('plot_id', (string) $plot->id)
            ->set('analysis_date', '2024-06-15')
            ->set('laboratory', 'Lab Agrícola SA')
            ->set('ph', '6.8')
            ->set('organic_matter', '2.5')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.soil-analyses.index'));

        $this->assertDatabaseHas('soil_analyses', [
            'viticulturist_id' => $v->id,
            'plot_id' => $plot->id,
            'laboratory' => 'Lab Agrícola SA',
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('plot_id', '')
            ->set('analysis_date', '')
            ->call('save')
            ->assertHasErrors(['plot_id', 'analysis_date']);
    }

    public function test_validates_ph_range(): void
    {
        $v = $this->makeViticulturist();
        $plot = Plot::factory()->forViticulturist($v)->create();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('plot_id', (string) $plot->id)
            ->set('analysis_date', '2024-06-15')
            ->set('ph', '15')
            ->call('save')
            ->assertHasErrors(['ph']);
    }

    public function test_mount_sets_analysis_date_to_today(): void
    {
        $v = $this->makeViticulturist();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->assertSet('analysis_date', now()->format('Y-m-d'));
    }

    public function test_cannot_assign_other_viticulturists_plot(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $foreignPlot = Plot::factory()->forViticulturist($other)->create();
        $this->actingAs($v);

        Livewire::test(Create::class)
            ->set('plot_id', (string) $foreignPlot->id)
            ->set('analysis_date', '2024-06-15')
            ->call('save')
            ->assertHasErrors(['plot_id']);
    }
}
