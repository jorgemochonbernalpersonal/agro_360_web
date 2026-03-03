<?php

namespace Tests\Feature\Viticulturist\PlotEnvironments;

use App\Livewire\Viticulturist\PlotEnvironments\Index;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotEnvironment;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AutonomousCommunitySeeder::class);
        $this->seed(\Database\Seeders\ProvinceSeeder::class);
        $this->seed(\Database\Seeders\MunicipalitySeeder::class);
    }

    private function makePlot(int $viticulturistId, string $name = 'Parcela Test'): Plot
    {
        return Plot::factory()->create([
            'viticulturist_id' => $viticulturistId,
            'name'             => $name,
        ]);
    }

    private function makePlotEnvironment(int $viticulturistId, int $campaignId, int $plotId, string $zoneType = ''): PlotEnvironment
    {
        return PlotEnvironment::create([
            'viticulturist_id'      => $viticulturistId,
            'campaign_id'           => $campaignId,
            'plot_id'               => $plotId,
            'protected_zone_partial'=> $zoneType !== '',
            'protection_zone_type'  => $zoneType ?: null,
            'erosion_risk'          => false,
            'water_intake_nearby'   => false,
            'protected_zone_total'  => false,
        ]);
    }

    public function test_index_shows_plot_environments(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = Campaign::getOrCreateActiveForYear($viticulturist->id);
        $plot          = $this->makePlot($viticulturist->id, 'Parcela-Visible-Test');

        $this->makePlotEnvironment($viticulturist->id, $campaign->id, $plot->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Parcela-Visible-Test');
    }

    public function test_delete_removes_record(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = Campaign::getOrCreateActiveForYear($viticulturist->id);
        $plot          = $this->makePlot($viticulturist->id);
        $env           = $this->makePlotEnvironment($viticulturist->id, $campaign->id, $plot->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $env->id);

        $this->assertDatabaseMissing('plot_environments', ['id' => $env->id]);
    }

    public function test_cannot_delete_other_viticulturists_environment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $campaign      = Campaign::getOrCreateActiveForYear($viticulturist->id);
        $plot          = $this->makePlot($viticulturist->id);
        $env           = $this->makePlotEnvironment($viticulturist->id, $campaign->id, $plot->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('delete', $env->id);
    }

    public function test_filter_by_campaign(): void
    {
        $viticulturist = $this->makeViticulturist();

        // Campaign A (2022) — mount will NOT select this one
        $campaignA = Campaign::create([
            'viticulturist_id' => $viticulturist->id,
            'year'             => 2022,
            'name'             => 'Campaña 2022',
        ]);
        // Campaign B (current year) — mount will select this one
        $campaignB = Campaign::getOrCreateActiveForYear($viticulturist->id);

        $plotA = $this->makePlot($viticulturist->id);
        $plotB = $this->makePlot($viticulturist->id);

        $this->makePlotEnvironment($viticulturist->id, $campaignA->id, $plotA->id, 'Zona-Campaña-A');
        $this->makePlotEnvironment($viticulturist->id, $campaignB->id, $plotB->id, 'Zona-Campaña-B');

        $this->actingAs($viticulturist);

        // Default filter = campaignB
        Livewire::test(Index::class)
            ->assertSee('Zona-Campaña-B')
            ->assertDontSee('Zona-Campaña-A')
            // Switch to campaignA
            ->set('filterCampaign', (string) $campaignA->id)
            ->assertSee('Zona-Campaña-A')
            ->assertDontSee('Zona-Campaña-B');
    }
}
