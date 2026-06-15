<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Harvest;

use App\Livewire\Viticulturist\DigitalNotebook\ShowHarvest;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\Province;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class ShowTest extends ViticulturistTestCase
{
    public function test_viticulturist_can_view_own_harvest(): void
    {
        $viticulturist = $this->makeViticulturist();
        $harvest = $this->makeHarvest($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(ShowHarvest::class, ['harvest' => $harvest->id])
            ->assertStatus(200);
    }

    public function test_cannot_view_other_viticulturist_harvest(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $harvest = $this->makeHarvest($other);

        $this->actingAs($viticulturist);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(ShowHarvest::class, ['harvest' => $harvest->id]);
    }

    public function test_mount_loads_harvest_data(): void
    {
        $viticulturist = $this->makeViticulturist();
        $harvest = $this->makeHarvest($viticulturist, ['total_weight' => 1800.0]);

        $this->actingAs($viticulturist);

        $component = Livewire::test(ShowHarvest::class, ['harvest' => $harvest->id]);

        $this->assertEquals($harvest->id, $component->get('harvest')->id);
        $this->assertEquals(1800.0, (float) $component->get('harvest')->total_weight);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeHarvest($viticulturist, array $harvestAttrs = []): Harvest
    {
        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        $plot = Plot::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id' => $prov->id,
            'municipality_id' => $muni->id,
            'active' => true,
        ]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 2.0,
            'status' => 'active',
        ]);

        $campaign = Campaign::factory()->active()->create([
            'viticulturist_id' => $viticulturist->id,
            'year' => now()->year,
        ]);

        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $viticulturist->id,
            'plot_id' => $plot->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now()->format('Y-m-d'),
            'is_locked' => false,
        ]);

        return Harvest::withoutEvents(fn () => Harvest::create(array_merge([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
            'harvest_start_date' => now()->format('Y-m-d'),
            'total_weight' => 2500.0,
            'destination_type' => 'winery',
            'status' => 'active',
        ], $harvestAttrs)));
    }
}
