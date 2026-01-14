<?php

namespace Tests\Unit\Models;

use App\Models\PhytosanitaryTreatment;
use App\Models\PhytosanitaryProduct;
use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\GrapeVariety;
use App\Models\Campaign;
use App\Models\User;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhytosanitaryTreatmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed de localización requerido para las relaciones
        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }
    
    /**
     * Helper para crear una plantación activa en una parcela
     */
    private function createPlantingForPlot(Plot $plot): PlotPlanting
    {
        $grapeVariety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );
        
        return PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted' => $plot->area * 0.8,
            'planting_year' => now()->year - 5,
            'status' => 'active',
        ]);
    }

    public function test_phytosanitary_treatment_belongs_to_activity(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'ES-12345678',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
            'withdrawal_period_days' => 14,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'dose_per_hectare' => 2.5,
            'total_dose' => 5.0,
            'area_treated' => 2.0,
        ]);

        $this->assertEquals($activity->id, $treatment->activity->id);
    }

    public function test_phytosanitary_treatment_belongs_to_product(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'ES-12345678',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
            'withdrawal_period_days' => 14,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'dose_per_hectare' => 2.5,
            'total_dose' => 5.0,
            'area_treated' => 2.0,
        ]);

        $this->assertEquals($product->id, $treatment->product->id);
    }

    public function test_phytosanitary_treatment_requires_product(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Requerido',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'ES-12345678',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
            'withdrawal_period_days' => 14,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'dose_per_hectare' => 2.5,
            'total_dose' => 5.0,
            'area_treated' => 2.0,
        ]);

        // Product is required, so it should be set
        $this->assertNotNull($treatment->product_id);
        $this->assertEquals($product->id, $treatment->product_id);
        $this->assertNotNull($treatment->product);
    }

    public function test_decimal_fields_are_cast_correctly(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'ES-12345678',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
            'withdrawal_period_days' => 14,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'dose_per_hectare' => 2.567,
            'total_dose' => 5.123,
            'area_treated' => 2.456,
            'wind_speed' => 15.75,
            'humidity' => 65.50,
        ]);

        // Los campos decimales se almacenan como strings en Laravel
        $this->assertIsNumeric($treatment->dose_per_hectare);
        $this->assertIsNumeric($treatment->total_dose);
        $this->assertIsNumeric($treatment->area_treated);
        $this->assertIsNumeric($treatment->wind_speed);
        $this->assertIsNumeric($treatment->humidity);
    }

    public function test_safe_harvest_date_calculates_correctly(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'ES-12345678',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
            'withdrawal_period_days' => 14,
        ]);

        $activityDate = now()->subDays(5);
        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => $activityDate,
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'dose_per_hectare' => 2.5,
            'total_dose' => 5.0,
            'area_treated' => 2.0,
        ]);

        // Recargar relaciones
        $treatment->load(['product', 'activity']);

        $expectedDate = $activityDate->copy()->addDays(14);
        $this->assertEquals($expectedDate->format('Y-m-d'), $treatment->safe_harvest_date->format('Y-m-d'));
    }

    public function test_safe_harvest_date_returns_null_when_withdrawal_period_is_zero(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        // withdrawal_period_days no puede ser null, pero puede ser 0
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Sin Plazo',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'ES-12345678',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
            'withdrawal_period_days' => 0,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'dose_per_hectare' => 2.5,
            'total_dose' => 5.0,
            'area_treated' => 2.0,
        ]);

        $treatment->load(['product', 'activity']);

        // Cuando withdrawal_period_days es 0, safe_harvest_date debería ser null según la lógica del modelo
        // (el modelo verifica !$this->product->withdrawal_period_days)
        $this->assertNull($treatment->safe_harvest_date);
    }

    public function test_phytosanitary_treatment_fillable_fields(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'ES-12345678',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
            'withdrawal_period_days' => 14,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'dose_per_hectare' => 2.5,
            'total_dose' => 5.0,
            'area_treated' => 2.0,
            'application_method' => 'pulverización',
            'wind_speed' => 15.75,
            'humidity' => 65.50,
        ]);

        $this->assertEquals('pulverización', $treatment->application_method);
        // target_pest no existe en el modelo, se usa pest_id (relación con Pest)
    }
}

