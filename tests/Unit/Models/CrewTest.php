<?php

namespace Tests\Unit\Models;

use App\Models\Crew;
use App\Models\User;
use App\Models\CrewMember;
use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\GrapeVariety;
use App\Models\Campaign;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeders necesarios para plots
        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    public function test_crew_belongs_to_viticulturist(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $this->assertEquals($viticulturist->id, $crew->viticulturist->id);
    }

    public function test_crew_belongs_to_winery(): void
    {
        $winery = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
            'winery_id' => $winery->id,
        ]);

        $this->assertEquals($winery->id, $crew->winery->id);
    }

    public function test_crew_can_have_null_winery(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
            'winery_id' => null,
        ]);

        $this->assertNull($crew->winery_id);
    }

    public function test_crew_has_many_members(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $member1 = User::factory()->create(['role' => 'viticulturist']);
        $member2 = User::factory()->create(['role' => 'viticulturist']);
        
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
        ]);

        CrewMember::create([
            'crew_id' => $crew->id,
            'viticulturist_id' => $member1->id,
            'assigned_by' => $viticulturist->id,
        ]);

        CrewMember::create([
            'crew_id' => $crew->id,
            'viticulturist_id' => $member2->id,
            'assigned_by' => $viticulturist->id,
        ]);

        $this->assertCount(2, $crew->members);
    }

    public function test_crew_has_many_activities(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        
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
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
        ]);

        AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
            'crew_id' => $crew->id,
        ]);

        AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
            'crew_id' => $crew->id,
        ]);

        $this->assertCount(2, $crew->activities);
    }

    public function test_scopeForViticulturist_filters_correctly(): void
    {
        $viticulturist1 = User::factory()->create(['role' => 'viticulturist']);
        $viticulturist2 = User::factory()->create(['role' => 'viticulturist']);
        
        $crew1 = Crew::create([
            'name' => 'Crew 1',
            'viticulturist_id' => $viticulturist1->id,
        ]);

        $crew2 = Crew::create([
            'name' => 'Crew 2',
            'viticulturist_id' => $viticulturist2->id,
        ]);

        $crews = Crew::forViticulturist($viticulturist1->id)->get();

        $this->assertCount(1, $crews);
        $this->assertEquals($crew1->id, $crews->first()->id);
    }

    public function test_scopeForWinery_filters_correctly(): void
    {
        $winery1 = User::factory()->create(['role' => 'winery']);
        $winery2 = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        
        $crew1 = Crew::create([
            'name' => 'Crew 1',
            'viticulturist_id' => $viticulturist->id,
            'winery_id' => $winery1->id,
        ]);

        $crew2 = Crew::create([
            'name' => 'Crew 2',
            'viticulturist_id' => $viticulturist->id,
            'winery_id' => $winery2->id,
        ]);

        $crews = Crew::forWinery($winery1->id)->get();

        $this->assertCount(1, $crews);
        $this->assertEquals($crew1->id, $crews->first()->id);
    }

    public function test_getMembersCountAttribute_returns_correct_count(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $member1 = User::factory()->create(['role' => 'viticulturist']);
        $member2 = User::factory()->create(['role' => 'viticulturist']);
        
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
        ]);

        CrewMember::create([
            'crew_id' => $crew->id,
            'viticulturist_id' => $member1->id,
            'assigned_by' => $viticulturist->id,
        ]);

        CrewMember::create([
            'crew_id' => $crew->id,
            'viticulturist_id' => $member2->id,
            'assigned_by' => $viticulturist->id,
        ]);

        $this->assertEquals(2, $crew->members_count);
    }

    public function test_getActivitiesCountAttribute_returns_correct_count(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        
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
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
        ]);

        AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
            'crew_id' => $crew->id,
        ]);

        AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
            'crew_id' => $crew->id,
        ]);

        $this->assertEquals(2, $crew->activities_count);
    }
}

