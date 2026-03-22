<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\EstimatedYields;

use App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields\Edit;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\EstimatedYield;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\Province;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    private function makePlot($viticulturist): Plot
    {
        $ac   = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        return Plot::factory()->create([
            'viticulturist_id'        => $viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id'             => $prov->id,
            'municipality_id'         => $muni->id,
            'active'                  => true,
        ]);
    }

    private function makeCampaign($viticulturist): Campaign
    {
        return Campaign::factory()->active()->create([
            'viticulturist_id' => $viticulturist->id,
            'year'             => now()->year,
        ]);
    }

    private function makePlanting(Plot $plot): PlotPlanting
    {
        return PlotPlanting::create([
            'plot_id'      => $plot->id,
            'area_planted' => 2.5,
            'status'       => 'active',
        ]);
    }

    private function makeEstimatedYield($viticulturist, PlotPlanting $planting, Campaign $campaign): EstimatedYield
    {
        return EstimatedYield::create([
            'plot_planting_id'            => $planting->id,
            'campaign_id'                 => $campaign->id,
            'estimated_by'                => $viticulturist->id,
            'estimated_yield_per_hectare' => 5000,
            'estimated_total_yield'       => 12500,
            'estimation_date'             => now()->format('Y-m-d'),
            'estimation_method'           => 'visual',
            'status'                      => 'draft',
            'estimation_round'            => 1,
            'potential_alcohol'           => 13.5,
        ]);
    }

    // ── mount: campos precargados ──────────────────────────────────────────────

    public function test_mount_fills_basic_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $ey            = $this->makeEstimatedYield($viticulturist, $planting, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['estimatedYield' => $ey->id])
            ->assertSet('plot_planting_id', $planting->id)
            ->assertSet('campaign_id', $campaign->id)
            ->assertSet('estimation_method', 'visual')
            ->assertSet('status', 'draft')
            ->assertSet('estimation_round', 1);
    }

    public function test_mount_fills_yield_and_alcohol(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $ey            = $this->makeEstimatedYield($viticulturist, $planting, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['estimatedYield' => $ey->id])
            ->assertSet('estimated_yield_per_hectare', '5000.000')
            ->assertSet('estimated_total_yield', '12500.000')
            ->assertSet('potential_alcohol', '13.50');
    }

    // ── update correcto ────────────────────────────────────────────────────────

    public function test_can_update_estimated_yield(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $ey            = $this->makeEstimatedYield($viticulturist, $planting, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['estimatedYield' => $ey->id])
            ->set('estimated_yield_per_hectare', 6000)
            ->set('estimated_total_yield', 15000)
            ->set('status', 'confirmed')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('estimated_yields', [
            'id'                          => $ey->id,
            'status'                      => 'confirmed',
        ]);
    }

    public function test_can_update_potential_alcohol(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $ey            = $this->makeEstimatedYield($viticulturist, $planting, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['estimatedYield' => $ey->id])
            ->set('potential_alcohol', 14.5)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertEquals('14.50', EstimatedYield::find($ey->id)->potential_alcohol);
    }

    public function test_can_clear_potential_alcohol_to_null(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $ey            = $this->makeEstimatedYield($viticulturist, $planting, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['estimatedYield' => $ey->id])
            ->set('potential_alcohol', '')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertNull(EstimatedYield::find($ey->id)->potential_alcohol);
    }

    // ── regla: ronda única ────────────────────────────────────────────────────

    public function test_duplicate_round_on_update_rejected(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $ey1           = $this->makeEstimatedYield($viticulturist, $planting, $campaign); // round 1

        // Crear ronda 2
        $ey2 = EstimatedYield::create([
            'plot_planting_id'            => $planting->id,
            'campaign_id'                 => $campaign->id,
            'estimated_by'                => $viticulturist->id,
            'estimated_yield_per_hectare' => 5500,
            'estimated_total_yield'       => 13750,
            'estimation_date'             => now()->format('Y-m-d'),
            'estimation_method'           => 'visual',
            'status'                      => 'draft',
            'estimation_round'            => 2,
        ]);

        $this->actingAs($viticulturist);

        // Intentar cambiar ey2 a ronda 1 (ya ocupada por ey1)
        $component = Livewire::test(Edit::class, ['estimatedYield' => $ey2->id])
            ->set('estimation_round', 1)
            ->call('update');

        // Debe seguir siendo ronda 2
        $this->assertEquals(2, EstimatedYield::find($ey2->id)->estimation_round);
    }

    // ── validaciones ──────────────────────────────────────────────────────────

    public function test_estimated_yield_per_hectare_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $ey            = $this->makeEstimatedYield($viticulturist, $planting, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['estimatedYield' => $ey->id])
            ->set('estimated_yield_per_hectare', '')
            ->call('update')
            ->assertHasErrors(['estimated_yield_per_hectare']);
    }

    public function test_potential_alcohol_max_25_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $ey            = $this->makeEstimatedYield($viticulturist, $planting, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['estimatedYield' => $ey->id])
            ->set('potential_alcohol', 30)
            ->call('update')
            ->assertHasErrors(['potential_alcohol']);
    }

    // ── autorización ──────────────────────────────────────────────────────────

    public function test_other_viticulturist_cannot_edit(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $ey            = $this->makeEstimatedYield($viticulturist, $planting, $campaign);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Edit::class, ['estimatedYield' => $ey->id]);
    }
}
