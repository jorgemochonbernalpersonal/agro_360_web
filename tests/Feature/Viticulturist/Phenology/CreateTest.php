<?php

namespace Tests\Feature\Viticulturist\Phenology;

use App\Models\Campaign;
use App\Models\GrapeVariety;
use App\Models\PhenologyObservation;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use App\Models\WineryViticulturist;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTest extends TestCase
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

    private function makeViticulturistWithPlantingAndCampaign(): array
    {
        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        $plot = Plot::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $variety = GrapeVariety::create(['name' => 'Tempranillo', 'code' => 'TEMP', 'color' => 'red']);

        $planting = PlotPlanting::create([
            'plot_id'          => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted'     => 1.5,
            'status'           => 'active',
            'density'          => 3000,
        ]);

        $campaign = Campaign::factory()->forViticulturist($viticulturist)->active()->create();

        return [$viticulturist, $planting, $campaign];
    }

    public function test_viticulturist_can_create_observation(): void
    {
        [$viticulturist, $planting, $campaign] = $this->makeViticulturistWithPlantingAndCampaign();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Viticulturist\Phenology\Create::class)
            ->set('plot_planting_id', $planting->id)
            ->set('campaign_id', $campaign->id)
            ->set('event', 'budbreak')
            ->set('obs_date', now()->toDateString())
            ->set('source', 'manual')
            ->set('confidence', 90)
            ->call('save')
            ->assertRedirect(route('viticulturist.phenology.index', ['filter_planting_id' => $planting->id]));

        $this->assertDatabaseHas('phenology_observations', [
            'plot_planting_id' => $planting->id,
            'campaign_id'      => $campaign->id,
            'event'            => 'budbreak',
            'viticulturist_id' => $viticulturist->id,
        ]);
    }

    public function test_updating_event_auto_fills_bbch_code(): void
    {
        [$viticulturist, $planting, $campaign] = $this->makeViticulturistWithPlantingAndCampaign();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Viticulturist\Phenology\Create::class)
            ->set('event', 'flowering')
            ->assertSet('bbch_code', PhenologyObservation::BBCH_CODES['flowering']);
    }

    public function test_updating_event_sets_pre_harvest_bbch_to_85(): void
    {
        [$viticulturist, $planting, $campaign] = $this->makeViticulturistWithPlantingAndCampaign();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Viticulturist\Phenology\Create::class)
            ->set('event', 'pre_harvest')
            ->assertSet('bbch_code', 85);
    }

    public function test_save_updates_existing_observation_for_same_event_planting_campaign(): void
    {
        [$viticulturist, $planting, $campaign] = $this->makeViticulturistWithPlantingAndCampaign();

        // Crear observación previa
        PhenologyObservation::create([
            'plot_planting_id' => $planting->id,
            'campaign_id'      => $campaign->id,
            'viticulturist_id' => $viticulturist->id,
            'event'            => 'veraison',
            'obs_date'         => '2024-08-01',
            'source'           => 'manual',
            'confidence'       => 70,
            'active'           => true,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Viticulturist\Phenology\Create::class)
            ->set('plot_planting_id', $planting->id)
            ->set('campaign_id', $campaign->id)
            ->set('event', 'veraison')
            ->set('obs_date', '2024-08-10')
            ->set('source', 'sensor')
            ->set('confidence', 95)
            ->call('save');

        // No debe duplicar la observación
        $this->assertDatabaseCount('phenology_observations', 1);
        $this->assertDatabaseHas('phenology_observations', [
            'event'      => 'veraison',
            'obs_date'   => '2024-08-10',
            'source'     => 'sensor',
            'confidence' => 95,
        ]);
    }

    public function test_required_fields_validation(): void
    {
        [$viticulturist, $planting, $campaign] = $this->makeViticulturistWithPlantingAndCampaign();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Viticulturist\Phenology\Create::class)
            ->set('plot_planting_id', '')
            ->set('campaign_id', '')
            ->set('event', '')
            ->set('obs_date', '')
            ->call('save')
            ->assertHasErrors(['plot_planting_id', 'campaign_id', 'event', 'obs_date']);
    }

    public function test_invalid_event_fails_validation(): void
    {
        [$viticulturist, $planting, $campaign] = $this->makeViticulturistWithPlantingAndCampaign();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Viticulturist\Phenology\Create::class)
            ->set('plot_planting_id', $planting->id)
            ->set('campaign_id', $campaign->id)
            ->set('event', 'evento_invalido')
            ->set('obs_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['event']);
    }

    public function test_confidence_must_be_between_0_and_100(): void
    {
        [$viticulturist, $planting, $campaign] = $this->makeViticulturistWithPlantingAndCampaign();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Viticulturist\Phenology\Create::class)
            ->set('plot_planting_id', $planting->id)
            ->set('campaign_id', $campaign->id)
            ->set('event', 'budbreak')
            ->set('obs_date', now()->toDateString())
            ->set('confidence', 150)
            ->call('save')
            ->assertHasErrors(['confidence']);
    }
}
