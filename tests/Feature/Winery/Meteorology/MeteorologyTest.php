<?php

namespace Tests\Feature\Winery\Meteorology;

use App\Livewire\Winery\Meteorology\Index;
use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class MeteorologyTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_renders_for_winery_without_plots(): void
    {
        Livewire::test(Index::class)
            ->assertStatus(200);
    }

    public function test_smoke_route_renders(): void
    {
        $this->get(route('winery.meteorology.index'))->assertOk();
    }

    public function test_load_weather_data_with_foreign_plot_returns_error(): void
    {
        $other = $this->makeOtherWinery();
        $foreignPlot = $this->makePlot($other);

        Livewire::test(Index::class)
            ->set('selectedPlot', (string) $foreignPlot->id)
            ->call('loadWeatherData')
            ->assertSet('error', __('Parcela no encontrada.'))
            ->assertSet('weather', []);
    }

    private function makePlot(User $owner): Plot
    {
        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        return Plot::factory()->create([
            'viticulturist_id' => $owner->id,
            'autonomous_community_id' => $ac->id,
            'province_id' => $prov->id,
            'municipality_id' => $muni->id,
            'active' => true,
        ]);
    }
}
