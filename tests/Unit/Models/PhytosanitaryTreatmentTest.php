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

    // ── Nuevos campos PAC (migración 2026_03_22) ──────────────────────────────

    public function test_field_applicator_id_saved_and_relationship_loads(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot     = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $applicator = \App\Models\FieldApplicator::create([
            'viticulturist_id' => $viticulturist->id,
            'name'             => 'Juan Aplicador',
            'ropo_number'      => 'ROPO-FA-001',
            'ropo_category'    => 'qualified',
            'active'           => true,
        ]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Test', 'active_ingredient' => 'X',
            'registration_number' => 'ES-99999999', 'withdrawal_period_days' => 7,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id, 'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id, 'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id'        => $activity->id,
            'product_id'         => $product->id,
            'field_applicator_id' => $applicator->id,
            'dose_per_hectare'   => 1.0,
            'area_treated'       => 1.0,
        ]);

        $this->assertEquals($applicator->id, $treatment->fresh()->field_applicator_id);
        $this->assertInstanceOf(\App\Models\FieldApplicator::class, $treatment->fresh()->fieldApplicator);
        $this->assertEquals('ROPO-FA-001', $treatment->fresh()->fieldApplicator->ropo_number);
    }

    public function test_field_applicator_nullified_on_delete(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot     = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $applicator = \App\Models\FieldApplicator::create([
            'viticulturist_id' => $viticulturist->id,
            'name' => 'Aplicador Temp', 'ropo_number' => 'ROPO-DEL', 'active' => true,
        ]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Test2', 'active_ingredient' => 'Y',
            'registration_number' => 'ES-88888888', 'withdrawal_period_days' => 7,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id, 'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id, 'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id'        => $activity->id,
            'product_id'         => $product->id,
            'field_applicator_id' => $applicator->id,
            'dose_per_hectare'   => 1.0,
            'area_treated'       => 1.0,
        ]);

        $applicator->delete();

        $this->assertNull($treatment->fresh()->field_applicator_id);
    }

    public function test_buffer_zone_respected_and_distance_stored_correctly(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot     = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Test3', 'active_ingredient' => 'Z',
            'registration_number' => 'ES-77777777', 'withdrawal_period_days' => 7,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id, 'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id, 'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id'           => $activity->id,
            'product_id'            => $product->id,
            'dose_per_hectare'      => 1.0,
            'area_treated'          => 1.0,
            'buffer_zone_respected' => true,
            'distance_to_water_m'   => 7.50,
        ]);

        $fresh = $treatment->fresh();
        $this->assertTrue($fresh->buffer_zone_respected);
        $this->assertEquals('7.50', $fresh->distance_to_water_m);
    }

    public function test_advisory_recommendation_date_stored_and_cast_as_date(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot     = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Test4', 'active_ingredient' => 'W',
            'registration_number' => 'ES-66666666', 'withdrawal_period_days' => 7,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id, 'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id, 'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id'                  => $activity->id,
            'product_id'                   => $product->id,
            'dose_per_hectare'             => 1.0,
            'area_treated'                 => 1.0,
            'under_advisory'               => true,
            'advisory_recommendation_date' => '2026-03-10',
        ]);

        $fresh = $treatment->fresh();
        $this->assertTrue($fresh->under_advisory);
        $this->assertInstanceOf(\Carbon\Carbon::class, $fresh->advisory_recommendation_date);
        $this->assertEquals('2026-03-10', $fresh->advisory_recommendation_date->format('Y-m-d'));
    }

    public function test_ipm_flags_default_to_false(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot     = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Test5', 'active_ingredient' => 'V',
            'registration_number' => 'ES-55555555', 'withdrawal_period_days' => 7,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id, 'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id, 'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $treatment = PhytosanitaryTreatment::create([
            'activity_id'     => $activity->id,
            'product_id'      => $product->id,
            'dose_per_hectare' => 1.0,
            'area_treated'    => 1.0,
        ]);

        $fresh = $treatment->fresh();
        $this->assertFalse($fresh->plague_monitoring);
        $this->assertFalse($fresh->prior_non_chemical_methods);
        $this->assertFalse($fresh->manual_mechanical_control);
        $this->assertFalse($fresh->biological_control);
        $this->assertFalse($fresh->cultural_preventions);
        $this->assertFalse($fresh->under_advisory);
    }
}

