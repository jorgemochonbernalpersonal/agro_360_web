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

class IndexTest extends TestCase
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

    private function makeViticulturistWithObservation(): array
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

        $plot    = Plot::factory()->create(['viticulturist_id' => $viticulturist->id]);
        $variety = GrapeVariety::create(['name' => 'Tempranillo', 'code' => 'TEMP', 'color' => 'red']);

        $planting = PlotPlanting::create([
            'plot_id'          => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted'     => 1.5,
            'status'           => 'active',
            'density'          => 3000,
        ]);

        $campaign = Campaign::factory()->forViticulturist($viticulturist)->active()->create();

        $observation = PhenologyObservation::create([
            'plot_planting_id' => $planting->id,
            'campaign_id'      => $campaign->id,
            'viticulturist_id' => $viticulturist->id,
            'event'            => 'flowering',
            'obs_date'         => '2024-06-01',
            'source'           => 'manual',
            'confidence'       => 85,
            'active'           => true,
        ]);

        return [$viticulturist, $planting, $campaign, $observation];
    }

    public function test_index_without_filter_planting_id_redirects_to_plantings(): void
    {
        [$viticulturist] = $this->makeViticulturistWithObservation();

        $this->actingAs($viticulturist)
            ->get(route('viticulturist.phenology.index'))
            ->assertRedirect(route('plots.plantings.index'));
    }

    public function test_index_with_filter_planting_id_renders_correctly(): void
    {
        [$viticulturist, $planting] = $this->makeViticulturistWithObservation();

        $this->actingAs($viticulturist)
            ->get(route('viticulturist.phenology.index', ['filter_planting_id' => $planting->id]))
            ->assertOk();
    }

    public function test_viticulturist_can_delete_own_observation(): void
    {
        [$viticulturist, $planting, $campaign, $observation] = $this->makeViticulturistWithObservation();

        $this->actingAs($viticulturist);

        Livewire::withQueryParams(['filter_planting_id' => $planting->id])
            ->test(\App\Livewire\Viticulturist\Phenology\Index::class)
            ->call('delete', $observation->id);

        $this->assertDatabaseMissing('phenology_observations', ['id' => $observation->id]);
    }

    public function test_viticulturist_cannot_delete_observation_from_other_viticulturist(): void
    {
        [$viticulturist, $planting, $campaign, $observation] = $this->makeViticulturistWithObservation();

        $other = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::withQueryParams(['filter_planting_id' => $planting->id])
            ->test(\App\Livewire\Viticulturist\Phenology\Index::class)
            ->call('delete', $observation->id);
    }
}
