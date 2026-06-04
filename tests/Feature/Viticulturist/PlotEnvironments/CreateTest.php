<?php

namespace Tests\Feature\Viticulturist\PlotEnvironments;

use App\Livewire\Viticulturist\PlotEnvironments\Create;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotEnvironment;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AutonomousCommunitySeeder::class);
        $this->seed(\Database\Seeders\ProvinceSeeder::class);
        $this->seed(\Database\Seeders\MunicipalitySeeder::class);
    }

    public function test_can_create_plot_environment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = $this->makePlot($viticulturist->id);
        $campaign = $this->makeCampaign($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('plot_id', (string) $plot->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.plot-environments.index'));

        $this->assertDatabaseHas('plot_environments', [
            'viticulturist_id' => $viticulturist->id,
            'plot_id' => $plot->id,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('campaign_id', '')
            ->set('plot_id', '')
            ->call('save')
            ->assertHasErrors(['campaign_id', 'plot_id']);
    }

    public function test_water_intake_distance_must_be_positive(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = $this->makePlot($viticulturist->id);
        $this->makeCampaign($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('plot_id', (string) $plot->id)
            ->set('water_intake_distance_m', '-1')
            ->call('save')
            ->assertHasErrors(['water_intake_distance_m']);
    }

    public function test_slope_pct_must_be_between_0_and_100(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = $this->makePlot($viticulturist->id);
        $this->makeCampaign($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('plot_id', (string) $plot->id)
            ->set('slope_pct', '150')
            ->call('save')
            ->assertHasErrors(['slope_pct']);
    }

    public function test_update_or_create_upserts_on_same_campaign_plot(): void
    {
        $viticulturist = $this->makeViticulturist();
        $plot = $this->makePlot($viticulturist->id);
        $campaign = $this->makeCampaign($viticulturist->id);

        $this->actingAs($viticulturist);

        // First save
        Livewire::test(Create::class)
            ->set('plot_id', (string) $plot->id)
            ->set('slope_pct', '10')
            ->call('save')
            ->assertHasNoErrors();

        // Second save with same campaign+plot combo
        Livewire::test(Create::class)
            ->set('plot_id', (string) $plot->id)
            ->set('slope_pct', '20')
            ->call('save')
            ->assertHasNoErrors();

        // Only 1 record exists (updateOrCreate)
        $this->assertSame(1, PlotEnvironment::where('plot_id', $plot->id)->where('campaign_id', $campaign->id)->count());
    }

    private function makePlot(int $viticulturistId, string $name = 'Parcela Test'): Plot
    {
        return Plot::factory()->create([
            'viticulturist_id' => $viticulturistId,
            'name' => $name,
        ]);
    }

    private function makeCampaign(int $viticulturistId, int $year = 2024): Campaign
    {
        return Campaign::getOrCreateActiveForYear($viticulturistId)
            ?? Campaign::create(['viticulturist_id' => $viticulturistId, 'year' => $year, 'name' => "Campaña $year"]);
    }
}
