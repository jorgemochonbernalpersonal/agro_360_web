<?php

namespace Tests\Unit\Models;

use App\Models\Campaign;
use App\Models\GrapeVariety;
use App\Models\PhenologyObservation;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhenologyObservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    private function makeObservation(array $overrides = []): PhenologyObservation
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $variety = GrapeVariety::create(['name' => 'Tempranillo', 'code' => 'TEMP', 'color' => 'red']);

        $planting = PlotPlanting::create([
            'plot_id'      => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 1.0,
            'status'       => 'active',
            'density'      => 3000,
        ]);

        $campaign = Campaign::factory()->forViticulturist($viticulturist)->active()->create();

        return PhenologyObservation::create(array_merge([
            'plot_planting_id' => $planting->id,
            'campaign_id'      => $campaign->id,
            'viticulturist_id' => $viticulturist->id,
            'event'            => 'budbreak',
            'obs_date'         => now()->toDateString(),
            'source'           => 'manual',
            'confidence'       => 90,
            'active'           => true,
        ], $overrides));
    }

    public function test_belongs_to_plot_planting_and_campaign(): void
    {
        $obs = $this->makeObservation();

        $this->assertNotNull($obs->plotPlanting);
        $this->assertNotNull($obs->campaign);
        $this->assertInstanceOf(PlotPlanting::class, $obs->plotPlanting);
        $this->assertInstanceOf(Campaign::class, $obs->campaign);
    }

    public function test_event_label_attribute_returns_correct_label(): void
    {
        $obs = $this->makeObservation(['event' => 'flowering']);

        $this->assertEquals('Floración', $obs->event_label);
    }

    public function test_source_label_attribute_returns_correct_label(): void
    {
        $obs = $this->makeObservation(['source' => 'sensor']);

        $this->assertEquals('Sensor IoT', $obs->source_label);
    }

    public function test_bbch_codes_constant_has_correct_values(): void
    {
        $this->assertEquals(7,  PhenologyObservation::BBCH_CODES['budbreak']);
        $this->assertEquals(16, PhenologyObservation::BBCH_CODES['shoot_growth']);
        $this->assertEquals(65, PhenologyObservation::BBCH_CODES['flowering']);
        $this->assertEquals(71, PhenologyObservation::BBCH_CODES['fruit_set']);
        $this->assertEquals(81, PhenologyObservation::BBCH_CODES['veraison']);
        $this->assertEquals(85, PhenologyObservation::BBCH_CODES['pre_harvest']);
        $this->assertEquals(89, PhenologyObservation::BBCH_CODES['harvest']);
    }

    public function test_scope_active_excludes_inactive_observations(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot          = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $variety       = GrapeVariety::create(['name' => 'Merlot', 'code' => 'MER', 'color' => 'red']);

        $planting = PlotPlanting::create([
            'plot_id'          => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted'     => 1.0,
            'status'           => 'active',
            'density'          => 3000,
        ]);

        $campaign = Campaign::factory()->forViticulturist($viticulturist)->active()->create();

        $base = [
            'plot_planting_id' => $planting->id,
            'campaign_id'      => $campaign->id,
            'viticulturist_id' => $viticulturist->id,
            'obs_date'         => now()->toDateString(),
            'source'           => 'manual',
            'confidence'       => 80,
        ];

        $active   = PhenologyObservation::create(array_merge($base, ['event' => 'budbreak',     'active' => true]));
        $inactive = PhenologyObservation::create(array_merge($base, ['event' => 'shoot_growth', 'active' => false]));

        $activeIds = PhenologyObservation::active()->pluck('id');

        $this->assertContains($active->id, $activeIds);
        $this->assertNotContains($inactive->id, $activeIds);
    }

    public function test_scope_chronological_orders_by_event_sequence(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot          = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $variety       = GrapeVariety::create(['name' => 'Garnacha', 'code' => 'GARN', 'color' => 'red']);
        $planting      = PlotPlanting::create([
            'plot_id'      => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 1.0,
            'status'       => 'active',
            'density'      => 3000,
        ]);
        $campaign = Campaign::factory()->forViticulturist($viticulturist)->active()->create();

        $base = [
            'plot_planting_id' => $planting->id,
            'campaign_id'      => $campaign->id,
            'viticulturist_id' => $viticulturist->id,
            'obs_date'         => now()->toDateString(),
            'source'           => 'manual',
            'confidence'       => 80,
            'active'           => true,
        ];

        // Insertados fuera de orden
        PhenologyObservation::create(array_merge($base, ['event' => 'harvest']));
        PhenologyObservation::create(array_merge($base, ['event' => 'budbreak']));
        PhenologyObservation::create(array_merge($base, ['event' => 'flowering']));

        $events = PhenologyObservation::where('plot_planting_id', $planting->id)
            ->chronological()
            ->pluck('event')
            ->all();

        $expectedOrder = array_keys(PhenologyObservation::EVENTS);
        $filtered      = array_values(array_intersect($expectedOrder, $events));

        $this->assertEquals($filtered, $events);
    }
}
