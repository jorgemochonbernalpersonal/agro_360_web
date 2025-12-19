<?php

namespace Tests\Unit\Models;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Models\Plot;
use App\Models\User;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgriculturalActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed de localización requerido por los factories de Plot
        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    public function test_activity_relationships_are_configured_correctly(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        $crew = Crew::create([
            'name' => 'Cuadrilla Test',
            'viticulturist_id' => $viticulturist->id,
        ]);
        $machinery = Machinery::create([
            'name' => 'Tractor Test',
            'type' => 'Tractor',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
            'crew_id' => $crew->id,
            'machinery_id' => $machinery->id,
        ]);

        $this->assertEquals($plot->id, $activity->plot->id);
        $this->assertEquals($viticulturist->id, $activity->viticulturist->id);
        $this->assertEquals($campaign->id, $activity->campaign->id);
        $this->assertEquals($crew->id, $activity->crew->id);
        $this->assertEquals($machinery->id, $activity->machinery->id);
    }

    public function test_scopes_filter_correctly(): void
    {
        $viticulturist1 = User::factory()->create(['role' => 'viticulturist']);
        $viticulturist2 = User::factory()->create(['role' => 'viticulturist']);

        $plot1 = Plot::factory()->state(['viticulturist_id' => $viticulturist1->id])->create();
        $plot2 = Plot::factory()->state(['viticulturist_id' => $viticulturist2->id])->create();

        $campaign1 = Campaign::factory()->create(['viticulturist_id' => $viticulturist1->id]);
        $campaign2 = Campaign::factory()->create(['viticulturist_id' => $viticulturist2->id]);

        $a1 = AgriculturalActivity::create([
            'plot_id' => $plot1->id,
            'viticulturist_id' => $viticulturist1->id,
            'campaign_id' => $campaign1->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $a2 = AgriculturalActivity::create([
            'plot_id' => $plot1->id,
            'viticulturist_id' => $viticulturist1->id,
            'campaign_id' => $campaign1->id,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
        ]);

        $a3 = AgriculturalActivity::create([
            'plot_id' => $plot2->id,
            'viticulturist_id' => $viticulturist2->id,
            'campaign_id' => $campaign2->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $this->assertEquals(
            [$a1->id, $a3->id],
            AgriculturalActivity::ofType('phytosanitary')->pluck('id')->sort()->values()->all()
        );

        $this->assertEquals(
            [$a1->id, $a2->id],
            AgriculturalActivity::forViticulturist($viticulturist1->id)->pluck('id')->sort()->values()->all()
        );

        $this->assertEquals(
            [$a1->id, $a2->id],
            AgriculturalActivity::forPlot($plot1->id)->pluck('id')->sort()->values()->all()
        );

        $this->assertEquals(
            [$a1->id, $a2->id],
            AgriculturalActivity::forCampaign($campaign1->id)->pluck('id')->sort()->values()->all()
        );
    }
}


