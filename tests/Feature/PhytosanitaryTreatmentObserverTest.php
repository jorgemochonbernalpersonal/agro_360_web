<?php

namespace Tests\Feature;

use App\Models\PhytosanitaryTreatment;
use App\Models\PhytosanitaryProduct;
use App\Models\ProductStock;
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

class PhytosanitaryTreatmentObserverTest extends TestCase
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

    public function test_observer_consumes_stock_when_treatment_is_created(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $user->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'registration_number' => 'ES-12345678',
            'withdrawal_period_days' => 14,
        ]);

        // Crear stock disponible
        $stock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 10.0,
            'unit' => 'L',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        // Crear tratamiento - el observer debería descontar el stock
        $treatment = PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'total_dose' => 3.5,
            'dose_per_hectare' => 1.0,
            'area_treated' => 3.5,
            'treatment_justification' => 'Test justification',
            'applicator_ropo_number' => 'ROPO-123',
            'reentry_period_days' => 3,
            'spray_volume' => 500.0,
        ]);

        $stock->refresh();
        
        // El stock debería haberse descontado
        $this->assertEquals(6.5, $stock->quantity);
        
        // Debería existir un movimiento de consumo
        $movement = $stock->movements()->where('movement_type', 'consumption')->first();
        $this->assertNotNull($movement);
        $this->assertEquals(-3.5, $movement->quantity_change);
        $this->assertEquals($treatment->id, $movement->treatment_id);
    }

    public function test_observer_handles_insufficient_stock(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $user->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'registration_number' => 'ES-12345678',
            'withdrawal_period_days' => 14,
        ]);

        // Crear stock insuficiente
        $stock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 2.0, // Menos de lo necesario
            'unit' => 'L',
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        // Crear tratamiento que requiere más stock del disponible
        $treatment = PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'total_dose' => 5.0, // Más de lo disponible
            'treatment_justification' => 'Test justification',
            'applicator_ropo_number' => 'ROPO-123',
            'reentry_period_days' => 3,
            'spray_volume' => 500.0,
        ]);

        $stock->refresh();
        
        // El stock debería haberse descontado solo lo disponible (2.0)
        $this->assertEquals(0, $stock->quantity);
        
        // Debería existir un movimiento de consumo
        $movement = $stock->movements()->where('movement_type', 'consumption')->first();
        $this->assertNotNull($movement);
        $this->assertEquals(-2.0, $movement->quantity_change);
    }

    public function test_observer_does_not_consume_when_no_total_dose(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $user->id])->create();
        $planting = $this->createPlantingForPlot($plot);
        $campaign = Campaign::factory()->create(['viticulturist_id' => $user->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'registration_number' => 'ES-12345678',
            'withdrawal_period_days' => 14,
        ]);

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 10.0,
        ]);

        $activity = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $user->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        // Crear tratamiento sin total_dose
        $treatment = PhytosanitaryTreatment::create([
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'total_dose' => null, // Sin dosis total
            'treatment_justification' => 'Test justification',
            'applicator_ropo_number' => 'ROPO-123',
            'reentry_period_days' => 3,
            'spray_volume' => 500.0,
        ]);

        $stock->refresh();
        
        // El stock no debería haberse descontado
        $this->assertEquals(10.0, $stock->quantity);
        
        // No debería existir ningún movimiento
        $movement = $stock->movements()->where('movement_type', 'consumption')->first();
        $this->assertNull($movement);
    }
}

