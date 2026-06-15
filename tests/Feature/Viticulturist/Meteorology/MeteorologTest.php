<?php

namespace Tests\Feature\Viticulturist\Meteorology;

use App\Livewire\Winery\Meteorology\Index;
use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class MeteorologTest extends ViticulturistTestCase
{
    public function test_renders_for_viticulturist_without_plots(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertStatus(200);
    }

    public function test_smoke_route_renders(): void
    {
        $viticulturist = $this->makeViticulturist();

        $this->actingAs($viticulturist)
            ->get(route('viticulturist.meteorology.index'))
            ->assertOk();
    }

    public function test_load_weather_data_with_foreign_plot_returns_error(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $foreignPlot = $this->makePlot($other);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('selectedPlot', (string) $foreignPlot->id)
            ->call('loadWeatherData')
            ->assertSet('error', __('Parcela no encontrada.'))
            ->assertSet('weather', []);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makePlot($viticulturist): Plot
    {
        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        return Plot::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id' => $prov->id,
            'municipality_id' => $muni->id,
            'active' => true,
        ]);
    }
}
