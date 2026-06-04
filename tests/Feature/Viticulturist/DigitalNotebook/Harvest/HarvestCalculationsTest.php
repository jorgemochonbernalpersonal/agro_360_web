<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Harvest;

use App\Livewire\Viticulturist\DigitalNotebook\CreateHarvest;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\ContainerType;
use App\Models\EstimatedYield;
use App\Models\Harvest;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\Province;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

/**
 * Tests para lógica de cálculos en CreateHarvest:
 * - calculateYield (rendimiento por hectárea)
 * - calculateTotalValue (peso × precio)
 * - loadControlPanelData con factor de edad
 * - updateControlPanelData (remaining, percentage, exceeds)
 */
class HarvestCalculationsTest extends ViticulturistTestCase
{
    private User $viticulturist;

    private Campaign $campaign;

    private Plot $plot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viticulturist = $this->makeViticulturist();
        $this->actingAs($this->viticulturist);

        $this->campaign = Campaign::factory()->active()->create([
            'viticulturist_id' => $this->viticulturist->id,
            'year' => now()->year,
        ]);

        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        $this->plot = Plot::factory()->create([
            'viticulturist_id' => $this->viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id' => $prov->id,
            'municipality_id' => $muni->id,
            'active' => true,
        ]);
    }

    // ── calculateYield ───────────────────────────────────────────────────────

    public function test_yield_calculated_as_weight_divided_by_area(): void
    {
        $planting = $this->makePlanting(['area_planted' => 2.5]);
        // Add a second planting to avoid auto-select triggering loadControlPanelData prematurely
        $this->makePlanting(['area_planted' => 1.0]);

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('total_weight', 5000);

        // 5000 / 2.5 = 2000 kg/ha
        $component->assertSet('yield_per_hectare', 2000.0);
    }

    public function test_yield_empty_when_no_weight(): void
    {
        $planting = $this->makePlanting();
        $this->makePlanting();

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('total_weight', '');

        $component->assertSet('yield_per_hectare', '');
    }

    public function test_yield_empty_when_no_planting_selected(): void
    {
        $this->makePlanting();
        $this->makePlanting();

        $component = Livewire::test(CreateHarvest::class)
            ->set('total_weight', 3000)
            ->set('plot_planting_id', '');

        $component->assertSet('yield_per_hectare', '');
    }

    public function test_yield_decimal_precision_3_decimals(): void
    {
        // 1000 / 3.0 = 333.333...
        $planting = $this->makePlanting(['area_planted' => 3.0]);
        $this->makePlanting();

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('total_weight', 1000);

        $component->assertSet('yield_per_hectare', 333.333);
    }

    // ── calculateTotalValue ──────────────────────────────────────────────────

    public function test_total_value_calculated_as_weight_times_price(): void
    {
        $planting = $this->makePlanting();
        $this->makePlanting();

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('total_weight', 2500)
            ->set('price_per_kg', 0.80);

        // 2500 × 0.80 = 2000.0
        $component->assertSet('total_value', 2000.0);
    }

    public function test_total_value_empty_when_no_price(): void
    {
        $planting = $this->makePlanting();
        $this->makePlanting();

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('total_weight', 2500)
            ->set('price_per_kg', '');

        $component->assertSet('total_value', '');
    }

    public function test_total_value_empty_when_no_weight(): void
    {
        $planting = $this->makePlanting();
        $this->makePlanting();

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('total_weight', '')
            ->set('price_per_kg', 1.50);

        $component->assertSet('total_value', '');
    }

    // ── effectiveHarvestLimitKg (factor de edad) ─────────────────────────────

    public function test_effective_limit_zero_for_plantings_under_3_years(): void
    {
        // Plantada en 2024, cosecha en 2026 → edad 2 → factor 0.0
        $planting = $this->makePlanting([
            'planting_year' => now()->year - 2,
            'harvest_limit_kg' => 10000,
        ]);

        $limit = $planting->effectiveHarvestLimitKg(now()->year);
        $this->assertEquals(0.0, $limit);
    }

    public function test_effective_limit_33_percent_at_age_3(): void
    {
        // Plantada en 2023, cosecha en 2026 → edad 3 → factor 0.33
        $planting = $this->makePlanting([
            'planting_year' => now()->year - 3,
            'harvest_limit_kg' => 10000,
        ]);

        $limit = $planting->effectiveHarvestLimitKg(now()->year);
        $this->assertEquals(3300.0, $limit);
    }

    public function test_effective_limit_75_percent_at_age_4(): void
    {
        // Plantada en 2022, cosecha en 2026 → edad 4 → factor 0.75
        $planting = $this->makePlanting([
            'planting_year' => now()->year - 4,
            'harvest_limit_kg' => 10000,
        ]);

        $limit = $planting->effectiveHarvestLimitKg(now()->year);
        $this->assertEquals(7500.0, $limit);
    }

    public function test_effective_limit_100_percent_at_age_5_plus(): void
    {
        // Plantada en 2015, cosecha en 2026 → edad 11 → factor 1.0
        $planting = $this->makePlanting([
            'planting_year' => now()->year - 11,
            'harvest_limit_kg' => 10000,
        ]);

        $limit = $planting->effectiveHarvestLimitKg(now()->year);
        $this->assertEquals(10000.0, $limit);
    }

    public function test_effective_limit_null_when_no_harvest_limit_set(): void
    {
        $planting = $this->makePlanting([
            'planting_year' => now()->year - 10,
            'harvest_limit_kg' => null,
        ]);

        $limit = $planting->effectiveHarvestLimitKg(now()->year);
        $this->assertNull($limit);
    }

    // ── loadControlPanelData ─────────────────────────────────────────────────

    public function test_control_panel_shows_harvest_limit_info(): void
    {
        $planting = $this->makePlanting([
            'planting_year' => now()->year - 10,
            'harvest_limit_kg' => 8000,
        ]);
        // Need a second planting so auto-select doesn't fire on updatedPlotId
        $this->makePlanting();

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id);

        $harvestLimit = $component->get('harvestLimitInfo');
        $this->assertNotNull($harvestLimit);
        $this->assertEquals(8000.0, $harvestLimit['limit']);
        $this->assertEquals(0, $harvestLimit['harvested']);
        $this->assertEquals(8000.0, $harvestLimit['remaining']);
        $this->assertEquals(0.0, $harvestLimit['percentage']);
        $this->assertEquals(100, $harvestLimit['age_factor']);
    }

    public function test_control_panel_accounts_for_prior_harvests(): void
    {
        $planting = $this->makePlanting([
            'planting_year' => now()->year - 10,
            'harvest_limit_kg' => 8000,
        ]);
        $this->makePlanting();

        // Crear cosecha previa de 3000 kg
        $this->createPriorHarvest($planting, 3000);

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id);

        $harvestLimit = $component->get('harvestLimitInfo');
        $this->assertNotNull($harvestLimit);
        $this->assertEquals(3000.0, $harvestLimit['harvested']);
        $this->assertEquals(5000.0, $harvestLimit['remaining']);
        $this->assertEquals(37.5, $harvestLimit['percentage']); // 3000/8000 * 100
    }

    public function test_control_panel_age_factor_for_young_planting(): void
    {
        // Edad 3 → factor 33% → límite efectivo 3300 de 10000
        $planting = $this->makePlanting([
            'planting_year' => now()->year - 3,
            'harvest_limit_kg' => 10000,
        ]);
        $this->makePlanting();

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id);

        $harvestLimit = $component->get('harvestLimitInfo');
        $this->assertNotNull($harvestLimit);
        $this->assertEquals(3300.0, $harvestLimit['limit']);
        $this->assertEquals(10000.0, $harvestLimit['raw_limit']);
        $this->assertEquals(33, $harvestLimit['age_factor']);
    }

    // ── updateControlPanelData ───────────────────────────────────────────────

    public function test_control_panel_updates_when_weight_changes(): void
    {
        $planting = $this->makePlanting([
            'planting_year' => now()->year - 10,
            'harvest_limit_kg' => 8000,
        ]);
        $this->makePlanting();

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('total_weight', 5000);

        $harvestLimit = $component->get('harvestLimitInfo');
        $this->assertEquals(5000.0, $harvestLimit['new_total']);
        $this->assertEquals(3000.0, $harvestLimit['new_remaining']);
        $this->assertEquals(62.5, $harvestLimit['new_percentage']); // 5000/8000*100
        $this->assertFalse($harvestLimit['exceeds']);
    }

    public function test_control_panel_detects_limit_exceeded(): void
    {
        $planting = $this->makePlanting([
            'planting_year' => now()->year - 10,
            'harvest_limit_kg' => 5000,
        ]);
        $this->makePlanting();

        // Cosecha previa de 3000 + nueva de 3000 = 6000 > 5000 límite
        $this->createPriorHarvest($planting, 3000);

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('total_weight', 3000);

        $harvestLimit = $component->get('harvestLimitInfo');
        $this->assertEquals(6000.0, $harvestLimit['new_total']);
        $this->assertEquals(0.0, $harvestLimit['new_remaining']); // max(0, 5000-6000)
        $this->assertTrue($harvestLimit['exceeds']);
    }

    public function test_control_panel_null_when_no_planting_selected(): void
    {
        $this->makePlanting();
        $this->makePlanting();

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', '');

        $component->assertSet('harvestLimitInfo', null);
        $component->assertSet('selectedPlanting', null);
    }

    // ── Yield variance ───────────────────────────────────────────────────────

    public function test_yield_variance_calculated_with_estimated_yield(): void
    {
        $planting = $this->makePlanting([
            'planting_year' => now()->year - 10,
            'area_planted' => 2.0,
        ]);
        $this->makePlanting();

        // Crear un rendimiento estimado
        EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $this->campaign->id,
            'estimated_by' => $this->viticulturist->id,
            'estimated_yield_per_hectare' => 3000,
            'estimated_total_yield' => 6000, // 3000 × 2 ha
            'estimation_date' => now(),
            'estimation_method' => 'visual',
            'status' => 'confirmed',
        ]);

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('total_weight', 7000);

        $variance = $component->get('yieldVarianceInfo');
        $this->assertNotNull($variance);
        $this->assertEquals(6000.0, (float) $variance['estimated']);
        $this->assertEquals(7000.0, $variance['actual']);
        $this->assertEquals(1000.0, $variance['variance']); // over by 1000
        $this->assertTrue($variance['is_over_yield']);
        $this->assertFalse($variance['is_under_yield']);
    }

    // ── Container auto-fill ──────────────────────────────────────────────────

    public function test_container_autofills_weight_with_available_capacity(): void
    {
        $planting = $this->makePlanting();
        $this->makePlanting();

        $containerType = ContainerType::firstOrCreate(['name' => 'Depósito']);

        $container = Container::create([
            'user_id' => $this->viticulturist->id,
            'name' => 'Depósito Test',
            'type_id' => $containerType->id,
            'capacity' => 5000,
            'used_capacity' => 2000,
            'archived' => false,
        ]);

        $component = Livewire::test(CreateHarvest::class)
            ->set('plot_id', $this->plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('container_id', $container->id);

        // Available = 5000 - 2000 = 3000
        $component->assertSet('total_weight', 3000.0);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makePlanting(array $attrs = []): PlotPlanting
    {
        return PlotPlanting::create(array_merge([
            'plot_id' => $this->plot->id,
            'area_planted' => 2.5,
            'status' => 'active',
        ], $attrs));
    }

    /**
     * Crea una cosecha previa (con withoutEvents para evitar observer side-effects)
     */
    private function createPriorHarvest(PlotPlanting $planting, float $weight, ?int $vintage = null): Harvest
    {
        $activity = AgriculturalActivity::create([
            'plot_id' => $this->plot->id,
            'viticulturist_id' => $this->viticulturist->id,
            'campaign_id' => $this->campaign->id,
            'activity_type' => 'harvest',
            'activity_date' => now()->format('Y-m-d'),
        ]);

        return Harvest::withoutEvents(fn () => Harvest::create([
            'activity_id' => $activity->id,
            'plot_planting_id' => $planting->id,
            'harvest_start_date' => now()->format('Y-m-d'),
            'vintage' => $vintage ?? now()->year,
            'total_weight' => $weight,
            'status' => 'active',
            'destination_type' => 'self_consumption',
        ]));
    }
}
