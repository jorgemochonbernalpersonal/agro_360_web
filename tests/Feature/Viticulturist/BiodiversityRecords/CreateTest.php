<?php

namespace Tests\Feature\Viticulturist\BiodiversityRecords;

use App\Livewire\Viticulturist\BiodiversityRecords\Create;
use App\Models\Plot;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_biodiversity_record(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = Plot::factory()->forViticulturist($viticulturist)->create();

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('plot_id', (string) $plot->id)
            ->set('record_type', 'cubierta_vegetal')
            ->set('record_date', '2024-06-15')
            ->set('area_m2', '500')
            ->set('species', 'Festuca rubra')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.biodiversity-records.index'));

        $this->assertDatabaseHas('biodiversity_records', [
            'viticulturist_id' => $viticulturist->id,
            'plot_id' => $plot->id,
            'record_type' => 'cubierta_vegetal',
            'area_m2' => 500,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('plot_id', '')
            ->set('record_date', '')
            ->call('save')
            ->assertHasErrors(['plot_id', 'record_date']);
    }

    public function test_mount_sets_today_as_record_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertSet('record_date', now()->format('Y-m-d'));
    }

    public function test_cannot_create_with_other_viticulturists_plot(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $otherPlot = Plot::factory()->forViticulturist($other)->create();

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('plot_id', (string) $otherPlot->id)
            ->set('record_date', '2024-06-15')
            ->call('save')
            ->assertHasErrors(['plot_id']);
    }
}
