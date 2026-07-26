<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\EstimatedYields;

use App\Livewire\Viticulturist\DigitalNotebook\EstimatedYields\Create;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\EstimatedYield;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\Province;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    // ── mount ──────────────────────────────────────────────────────────────────

    public function test_component_renders(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertOk();
    }

    public function test_mount_sets_today_as_estimation_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertSet('estimation_date', now()->format('Y-m-d'));
    }

    public function test_mount_sets_campaign_id(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertSet('campaign_id', $campaign->id);
    }

    public function test_mount_sets_default_estimation_method_visual(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertSet('estimation_method', 'visual');
    }

    // ── save correcto ──────────────────────────────────────────────────────────

    public function test_can_save_estimated_yield(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $planting = $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('campaign_id', $campaign->id)
            ->set('plot_planting_id', $planting->id)
            ->set('estimation_round', 1)
            ->set('estimation_method', 'visual')
            ->set('estimation_date', now()->format('Y-m-d'))
            ->set('estimated_yield_per_hectare', 5000)
            ->set('estimated_total_yield', 12500)
            ->set('status', 'draft')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('estimated_yields', [
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimation_round' => 1,
            'estimation_method' => 'visual',
            'status' => 'draft',
        ]);
    }

    public function test_can_save_with_sampling_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $planting = $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('campaign_id', $campaign->id)
            ->set('plot_planting_id', $planting->id)
            ->set('estimation_round', 2)
            ->set('estimation_method', 'sampling')
            ->set('estimation_date', now()->format('Y-m-d'))
            ->set('estimated_yield_per_hectare', 6000)
            ->set('estimated_total_yield', 15000)
            ->set('status', 'confirmed')
            ->set('bunches_per_plant', 12.5)
            ->set('bunch_weight_grams', 180.0)
            ->set('health_percentage', 95.0)
            ->set('potential_alcohol', 13.5)
            ->call('save')
            ->assertHasNoErrors();

        $ey = EstimatedYield::latest()->first();
        $this->assertEquals('13.50', $ey->potential_alcohol);
        $this->assertEquals('confirmed', $ey->status);
    }

    // ── regla: ronda única por plantación y campaña ───────────────────────────

    public function test_duplicate_round_is_rejected(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $planting = $this->makePlanting($plot);

        // Crear una estimación de ronda 1 previa
        EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_by' => $viticulturist->id,
            'estimated_yield_per_hectare' => 5000,
            'estimated_total_yield' => 12500,
            'estimation_date' => now()->format('Y-m-d'),
            'estimation_method' => 'visual',
            'status' => 'draft',
            'estimation_round' => 1,
        ]);

        $this->actingAs($viticulturist);

        // Intentar crear otra de ronda 1 para la misma plantación/campaña
        $component = Livewire::test(Create::class)
            ->set('campaign_id', $campaign->id)
            ->set('plot_planting_id', $planting->id)
            ->set('estimation_round', 1)
            ->set('estimation_method', 'visual')
            ->set('estimation_date', now()->format('Y-m-d'))
            ->set('estimated_yield_per_hectare', 5000)
            ->set('estimated_total_yield', 12500)
            ->set('status', 'draft')
            ->call('save');

        // El save devuelve sin crear, sin errores de validación pero sin nuevo registro
        $this->assertDatabaseCount('estimated_yields', 1);
    }

    // ── validaciones ──────────────────────────────────────────────────────────

    public function test_estimated_yield_per_hectare_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $planting = $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('campaign_id', $campaign->id)
            ->set('plot_planting_id', $planting->id)
            ->set('estimation_round', 1)
            ->set('estimation_method', 'visual')
            ->set('estimation_date', now()->format('Y-m-d'))
            ->set('estimated_yield_per_hectare', '')
            ->set('estimated_total_yield', 12500)
            ->set('status', 'draft')
            ->call('save')
            ->assertHasErrors(['estimated_yield_per_hectare']);
    }

    public function test_estimated_total_yield_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $planting = $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('campaign_id', $campaign->id)
            ->set('plot_planting_id', $planting->id)
            ->set('estimation_round', 1)
            ->set('estimation_method', 'visual')
            ->set('estimation_date', now()->format('Y-m-d'))
            ->set('estimated_yield_per_hectare', 5000)
            ->set('estimated_total_yield', '')
            ->set('status', 'draft')
            ->call('save')
            ->assertHasErrors(['estimated_total_yield']);
    }

    public function test_potential_alcohol_max_25(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $planting = $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('campaign_id', $campaign->id)
            ->set('plot_planting_id', $planting->id)
            ->set('estimation_round', 1)
            ->set('estimation_method', 'visual')
            ->set('estimation_date', now()->format('Y-m-d'))
            ->set('estimated_yield_per_hectare', 5000)
            ->set('estimated_total_yield', 12500)
            ->set('status', 'draft')
            ->set('potential_alcohol', 30)
            ->call('save')
            ->assertHasErrors(['potential_alcohol']);
    }

    // ── autorización ──────────────────────────────────────────────────────────

    public function test_planting_from_other_viticulturist_denied(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($other); // parcela del otro
        $planting = $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        $this->assertFalse(
            $viticulturist->can('update', $plot)
        );
    }
    // ── Helpers ────────────────────────────────────────────────────────────────

    private function makePlot($viticulturist): Plot
    {
        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        return Plot::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id' => $prov->id,
            'municipality_id' => $muni->id,
            'active' => true,
        ]);
    }

    private function makeCampaign($viticulturist): Campaign
    {
        // makeViticulturist() ya dispara UserObserver, que auto-crea la campaña
        // activa del año en curso: reutilizarla evita duplicar campañas activas
        // (sin unique constraint en BD, un first() posterior sería no determinista).
        $campaign = Campaign::where('viticulturist_id', $viticulturist->id)->first();

        if ($campaign) {
            $campaign->update(['year' => now()->year, 'active' => true]);

            return $campaign;
        }

        return Campaign::factory()->active()->create([
            'viticulturist_id' => $viticulturist->id,
            'year' => now()->year,
        ]);
    }

    private function makePlanting(Plot $plot): PlotPlanting
    {
        return PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 2.5,
            'status' => 'active',
        ]);
    }
}
