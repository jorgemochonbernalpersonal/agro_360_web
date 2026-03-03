<?php

namespace Tests\Feature\Viticulturist\PlotEnvironments;

use App\Livewire\Viticulturist\PlotEnvironments\Edit;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotEnvironment;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AutonomousCommunitySeeder::class);
        $this->seed(\Database\Seeders\ProvinceSeeder::class);
        $this->seed(\Database\Seeders\MunicipalitySeeder::class);
    }

    private function makePlot(int $viticulturistId): Plot
    {
        return Plot::factory()->create(['viticulturist_id' => $viticulturistId]);
    }

    private function makeCampaign(int $viticulturistId, int $year = 2024): Campaign
    {
        return Campaign::create([
            'viticulturist_id' => $viticulturistId,
            'year'             => $year,
            'name'             => "Campaña $year",
        ]);
    }

    private function makePlotEnvironment(int $viticulturistId, int $campaignId, int $plotId): PlotEnvironment
    {
        return PlotEnvironment::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id'      => $campaignId,
            'plot_id'          => $plotId,
            'slope_pct'        => 15,
            'erosion_risk'     => false,
            'water_intake_nearby'   => false,
            'protected_zone_total'  => false,
            'protected_zone_partial'=> false,
        ]);
    }

    public function test_mount_fills_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist->id);
        $plot          = $this->makePlot($viticulturist->id);
        $env           = $this->makePlotEnvironment($viticulturist->id, $campaign->id, $plot->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['plotEnvironment' => $env])
            ->assertSet('campaign_id', (string) $campaign->id)
            ->assertSet('plot_id', (string) $plot->id)
            ->assertSet('slope_pct', '15.00');
    }

    public function test_can_update_plot_environment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist->id);
        $plot          = $this->makePlot($viticulturist->id);
        $env           = $this->makePlotEnvironment($viticulturist->id, $campaign->id, $plot->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['plotEnvironment' => $env])
            ->set('slope_pct', '25')
            ->set('erosion_risk', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.plot-environments.index'));

        $this->assertDatabaseHas('plot_environments', [
            'id'          => $env->id,
            'erosion_risk'=> true,
        ]);
    }

    public function test_duplicate_campaign_plot_rejected_on_change(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign1     = $this->makeCampaign($viticulturist->id, 2022);
        $campaign2     = $this->makeCampaign($viticulturist->id, 2023);
        $plot          = $this->makePlot($viticulturist->id);

        // env1: campaign1 + plot
        $env1 = $this->makePlotEnvironment($viticulturist->id, $campaign1->id, $plot->id);
        // env2: campaign2 + same plot
        $this->makePlotEnvironment($viticulturist->id, $campaign2->id, $plot->id);

        $this->actingAs($viticulturist);

        // Try to change env1's campaign to campaign2 (conflict with env2)
        Livewire::test(Edit::class, ['plotEnvironment' => $env1])
            ->set('campaign_id', (string) $campaign2->id)
            ->call('save')
            ->assertHasErrors(['campaign_id']);
    }

    public function test_same_combo_on_own_record_is_ok(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist->id);
        $plot          = $this->makePlot($viticulturist->id);
        $env           = $this->makePlotEnvironment($viticulturist->id, $campaign->id, $plot->id);

        $this->actingAs($viticulturist);

        // Save without changing campaign+plot → no uniqueness error (ignore own ID)
        Livewire::test(Edit::class, ['plotEnvironment' => $env])
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_cannot_edit_other_viticulturists_environment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist->id);
        $plot          = $this->makePlot($viticulturist->id);
        $env           = $this->makePlotEnvironment($viticulturist->id, $campaign->id, $plot->id);

        $this->actingAs($other)
            ->get(route('viticulturist.plot-environments.edit', $env))
            ->assertStatus(403);
    }
}
