<?php

namespace Tests\Unit\Models;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Machinery;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\GrapeVariety;
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
    
    /**
     * Helper para crear una plantación activa en una parcela
     */
    private function createPlantingForPlot(Plot $plot): PlotPlanting
    {
        $grapeVariety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );
        
        return PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted' => $plot->area * 0.8,
            'planting_year' => now()->year - 5,
            'status' => 'active',
        ]);
    }

    public function test_activity_relationships_are_configured_correctly(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        
        // Crear una plantación activa para la parcela
        $grapeVariety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted' => $plot->area * 0.8,
            'planting_year' => now()->year - 5,
            'status' => 'active',
        ]);
        
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
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
            'crew_id' => $crew->id,
            'machinery_id' => $machinery->id,
        ]);

        $this->assertEquals($plot->id, $activity->plot->id);
        $this->assertEquals($planting->id, $activity->plotPlanting->id);
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
        
        $planting1 = $this->createPlantingForPlot($plot1);
        $planting2 = $this->createPlantingForPlot($plot2);

        $campaign1 = Campaign::factory()->create(['viticulturist_id' => $viticulturist1->id]);
        $campaign2 = Campaign::factory()->create(['viticulturist_id' => $viticulturist2->id]);

        $a1 = AgriculturalActivity::create([
            'plot_id' => $plot1->id,
            'plot_planting_id' => $planting1->id,
            'viticulturist_id' => $viticulturist1->id,
            'campaign_id' => $campaign1->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $a2 = AgriculturalActivity::create([
            'plot_id' => $plot1->id,
            'plot_planting_id' => $planting1->id,
            'viticulturist_id' => $viticulturist1->id,
            'campaign_id' => $campaign1->id,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
        ]);

        $a3 = AgriculturalActivity::create([
            'plot_id' => $plot2->id,
            'plot_planting_id' => $planting2->id,
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

    public function test_activity_can_have_nullable_relationships(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => now(),
            'crew_id' => null,
            'machinery_id' => null,
        ]);

        $this->assertNull($activity->crew);
        $this->assertNull($activity->machinery);
        $this->assertNotNull($activity->plot);
        $this->assertNotNull($activity->campaign);
    }

    public function test_activity_has_harvest_relationship(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now(),
        ]);

        $harvest = \App\Models\Harvest::factory()->create([
            'activity_id' => $activity->id,
        ]);

        $this->assertCount(1, $activity->harvests);
        $this->assertEquals($harvest->id, $activity->harvests->first()->id);
    }

    public function test_activity_has_phytosanitary_treatment_relationship(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = \App\Models\PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => \App\Models\PhytosanitaryProduct::factory()->create()->id,
        ]);

        $this->assertNotNull($activity->phytosanitaryTreatment);
        $this->assertEquals($treatment->id, $activity->phytosanitaryTreatment->id);
    }

    public function test_activity_has_fertilization_relationship(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
        ]);

        $fertilization = \App\Models\Fertilization::create([
            'activity_id' => $activity->id,
            'fertilizer_type' => 'organic',
        ]);

        $this->assertNotNull($activity->fertilization);
        $this->assertEquals($fertilization->id, $activity->fertilization->id);
    }

    public function test_activity_has_irrigation_relationship(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => now(),
        ]);

        $irrigation = \App\Models\Irrigation::create([
            'activity_id' => $activity->id,
            'water_amount' => 500.0,
        ]);

        $this->assertNotNull($activity->irrigation);
        $this->assertEquals($irrigation->id, $activity->irrigation->id);
    }

    public function test_scope_for_date_range_filters_correctly(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity1 = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now()->subDays(10),
        ]);

        $activity2 = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now()->subDays(5),
        ]);

        $activity3 = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now()->addDays(5),
        ]);

        $startDate = now()->subDays(7);
        $endDate = now()->subDays(3);

        $results = AgriculturalActivity::whereBetween('activity_date', [$startDate, $endDate])->get();

        $this->assertTrue($results->contains('id', $activity2->id));
        $this->assertFalse($results->contains('id', $activity1->id));
        $this->assertFalse($results->contains('id', $activity3->id));
    }

    public function test_activity_has_cultural_work_relationship(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'cultural',
            'activity_date' => now(),
        ]);

        $culturalWork = \App\Models\CulturalWork::create([
            'activity_id' => $activity->id,
            'work_type' => 'pruning',
        ]);

        $this->assertNotNull($activity->culturalWork);
        $this->assertEquals($culturalWork->id, $activity->culturalWork->id);
    }

    public function test_activity_has_observation_relationship(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'observation',
            'activity_date' => now(),
        ]);

        $observation = \App\Models\Observation::create([
            'activity_id' => $activity->id,
            'observation_type' => 'pest',
            'description' => 'Test observation',
        ]);

        $this->assertNotNull($activity->observation);
        $this->assertEquals($observation->id, $activity->observation->id);
    }

    public function test_activity_can_have_crew_member_relationship(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $worker = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $crewMember = \App\Models\CrewMember::create([
            'viticulturist_id' => $worker->id,
            'assigned_by' => $viticulturist->id,
            'crew_id' => null, // Trabajador individual
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => now(),
            'crew_member_id' => $crewMember->id,
        ]);

        $this->assertNotNull($activity->crewMember);
        $this->assertEquals($crewMember->id, $activity->crewMember->id);
    }

    public function test_activity_can_have_both_crew_and_crew_member(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $worker = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $crewMember = \App\Models\CrewMember::create([
            'viticulturist_id' => $worker->id,
            'assigned_by' => $viticulturist->id,
            'crew_id' => $crew->id,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
            'crew_id' => $crew->id,
            'crew_member_id' => $crewMember->id,
        ]);

        $this->assertNotNull($activity->crew);
        $this->assertNotNull($activity->crewMember);
        $this->assertEquals($crew->id, $activity->crew->id);
        $this->assertEquals($crewMember->id, $activity->crewMember->id);
    }

    public function test_activity_date_is_cast_to_date(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => '2025-01-15',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $activity->activity_date);
        $this->assertEquals('2025-01-15', $activity->activity_date->format('Y-m-d'));
    }

    public function test_scope_for_campaign_filters_correctly(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign1 = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        $campaign2 = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $activity1 = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign1->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $activity2 = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign2->id,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
        ]);

        $results = AgriculturalActivity::forCampaign($campaign1->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($activity1->id, $results->first()->id);
    }
}


