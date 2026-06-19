<?php

namespace Tests\Feature\Producer;

use App\Livewire\Producer\Meteorology\Index;
use App\Models\Plot;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

class MeteorologyTest extends ProducerTestCase
{
    public function test_renders_for_producer_without_plots(): void
    {
        $producer = $this->makeProducer();

        // No plots → no WeatherService calls → no external HTTP
        Livewire::actingAs($producer)
            ->test(Index::class)
            ->assertStatus(200)
            ->assertSet('selectedPlot', '')
            ->assertSet('weather', []);
    }

    public function test_route_accessible_for_producer(): void
    {
        $this->actingAs($this->makeProducer())
            ->get(route('producer.meteorology.index'))
            ->assertOk();
    }

    public function test_viticulturist_cannot_access(): void
    {
        $this->actingAs($this->makeViticulturist())
            ->get(route('producer.meteorology.index'))
            ->assertForbidden();
    }

    public function test_foreign_plot_returns_error_and_empty_weather(): void
    {
        $producer = $this->makeProducer();
        $other = $this->makeOtherProducer();
        $foreignPlot = Plot::factory()->create(['viticulturist_id' => $other->id]);

        Livewire::actingAs($producer)
            ->test(Index::class)
            ->set('selectedPlot', (string) $foreignPlot->id)
            ->call('loadWeatherData')
            ->assertSet('error', __('Parcela no encontrada.'))
            ->assertSet('weather', []);
    }

    public function test_plots_in_render_scoped_to_own_producer(): void
    {
        $producer = $this->makeProducer();
        $other = $this->makeOtherProducer();

        $ownPlot = Plot::factory()->create(['viticulturist_id' => $producer->id]);
        Plot::factory()->create(['viticulturist_id' => $other->id]);

        $component = Livewire::actingAs($producer)->test(Index::class);
        $plots = collect($component->viewData('plots'));

        $this->assertTrue($plots->contains('id', $ownPlot->id));
        $this->assertFalse($plots->contains('viticulturist_id', $other->id));
    }
}
