<?php

namespace Tests\Feature;

use App\Models\Crew;
use App\Models\User;
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

class CrewPolicyTest extends TestCase
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

    public function test_viticulturist_can_view_own_crews(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $this->actingAs($viticulturist)
            ->assertTrue($viticulturist->can('view', $crew));
    }

    public function test_viticulturist_cannot_view_other_crews(): void
    {
        $viticulturist1 = User::factory()->create(['role' => 'viticulturist']);
        $viticulturist2 = User::factory()->create(['role' => 'viticulturist']);
        
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist1->id,
        ]);

        $this->actingAs($viticulturist2)
            ->assertFalse($viticulturist2->can('view', $crew));
    }

    public function test_viticulturist_can_create_crews(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $this->actingAs($viticulturist)
            ->assertTrue($viticulturist->can('create', Crew::class));
    }

    public function test_non_viticulturist_cannot_create_crews(): void
    {
        $winery = User::factory()->create(['role' => 'winery']);

        $this->actingAs($winery)
            ->assertFalse($winery->can('create', Crew::class));
    }

    public function test_viticulturist_can_update_own_crew(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $this->actingAs($viticulturist)
            ->assertTrue($viticulturist->can('update', $crew));
    }

    public function test_viticulturist_cannot_update_other_crew(): void
    {
        $viticulturist1 = User::factory()->create(['role' => 'viticulturist']);
        $viticulturist2 = User::factory()->create(['role' => 'viticulturist']);
        
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist1->id,
        ]);

        $this->actingAs($viticulturist2)
            ->assertFalse($viticulturist2->can('update', $crew));
    }

    public function test_viticulturist_can_delete_own_crew_without_activities(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $this->actingAs($viticulturist)
            ->assertTrue($viticulturist->can('delete', $crew));
    }

    public function test_viticulturist_cannot_delete_crew_with_activities(): void
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

        // Crear una actividad asociada
        AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
            'crew_id' => $crew->id,
        ]);

        $this->actingAs($viticulturist)
            ->assertFalse($viticulturist->can('delete', $crew));
    }

    public function test_viticulturist_cannot_delete_other_crew(): void
    {
        $viticulturist1 = User::factory()->create(['role' => 'viticulturist']);
        $viticulturist2 = User::factory()->create(['role' => 'viticulturist']);
        
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist1->id,
        ]);

        $this->actingAs($viticulturist2)
            ->assertFalse($viticulturist2->can('delete', $crew));
    }

    public function test_viticulturist_can_view_any_crews(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $this->actingAs($viticulturist)
            ->assertTrue($viticulturist->can('viewAny', Crew::class));
    }

    public function test_viticulturist_can_create_crew_without_winery(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        
        $crew = Crew::create([
            'name' => 'Test Crew',
            'viticulturist_id' => $viticulturist->id,
            'winery_id' => null,
        ]);

        $this->actingAs($viticulturist)
            ->assertTrue($viticulturist->can('view', $crew));
    }
}

