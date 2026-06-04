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

class EditTest extends TestCase
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

    public function test_viticulturist_can_update_own_observation(): void
    {
        [$viticulturist, $planting, $campaign, $observation] = $this->makeObservationForViticulturist();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Viticulturist\Phenology\Edit::class, ['observation' => $observation])
            ->set('obs_date', '2024-04-10')
            ->set('confidence', 95)
            ->set('notes', 'Notas actualizadas')
            ->call('update')
            ->assertRedirect(route('viticulturist.phenology.index', ['filter_planting_id' => $planting->id]));

        $this->assertDatabaseHas('phenology_observations', [
            'id' => $observation->id,
            'obs_date' => '2024-04-10',
            'confidence' => 95,
            'notes' => 'Notas actualizadas',
        ]);
    }

    public function test_cannot_edit_observation_of_other_viticulturist(): void
    {
        [$viticulturist, $planting, $campaign, $observation] = $this->makeObservationForViticulturist();

        $other = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($other);

        $response = $this->get(route('viticulturist.phenology.edit', $observation));
        $response->assertStatus(403);
    }

    public function test_updating_event_auto_fills_bbch_code(): void
    {
        [$viticulturist, $planting, $campaign, $observation] = $this->makeObservationForViticulturist();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Viticulturist\Phenology\Edit::class, ['observation' => $observation])
            ->set('event', 'veraison')
            ->assertSet('bbch_code', PhenologyObservation::BBCH_CODES['veraison']);
    }

    public function test_validation_rejects_invalid_event(): void
    {
        [$viticulturist, $planting, $campaign, $observation] = $this->makeObservationForViticulturist();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Viticulturist\Phenology\Edit::class, ['observation' => $observation])
            ->set('event', 'evento_invalido')
            ->call('update')
            ->assertHasErrors(['event']);
    }

    public function test_validation_rejects_empty_required_fields(): void
    {
        [$viticulturist, $planting, $campaign, $observation] = $this->makeObservationForViticulturist();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Viticulturist\Phenology\Edit::class, ['observation' => $observation])
            ->set('obs_date', '')
            ->set('campaign_id', '')
            ->call('update')
            ->assertHasErrors(['obs_date', 'campaign_id']);
    }

    private function makeObservationForViticulturist(): array
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $plot = Plot::factory()->create(['viticulturist_id' => $viticulturist->id]);
        $variety = GrapeVariety::firstOrCreate(['name' => 'Verdejo'], ['code' => 'VER', 'color' => 'white']);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 1.0,
            'status' => 'active',
            'density' => 3000,
        ]);

        $campaign = Campaign::factory()->forViticulturist($viticulturist)->active()->create();

        $observation = PhenologyObservation::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'viticulturist_id' => $viticulturist->id,
            'event' => 'budbreak',
            'obs_date' => '2024-04-01',
            'source' => 'manual',
            'confidence' => 80,
            'active' => true,
        ]);

        return [$viticulturist, $planting, $campaign, $observation];
    }
}
