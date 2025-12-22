<?php

namespace Tests\Unit\Models;

use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
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

class PhytosanitaryProductTest extends TestCase
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

    public function test_phytosanitary_product_has_many_treatments(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        
        $grapeVariety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $grapeVariety->id,
            'area_planted' => $plot->area * 0.8,
            'planting_year' => now()->year - 5,
            'status' => 'active',
        ]);
        
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'REG-12345',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
            'toxicity_class' => 'III',
            'withdrawal_period_days' => 14,
        ]);

        $activity1 = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        $activity2 = AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
        ]);

        PhytosanitaryTreatment::create([
            'activity_id' => $activity1->id,
            'product_id' => $product->id,
            'dose_per_hectare' => 2.5,
            'total_dose' => 5.0,
            'area_treated' => 2.0,
        ]);

        PhytosanitaryTreatment::create([
            'activity_id' => $activity2->id,
            'product_id' => $product->id,
            'dose_per_hectare' => 3.0,
            'total_dose' => 6.0,
            'area_treated' => 2.0,
        ]);

        $this->assertCount(2, $product->treatments);
        $this->assertTrue($product->treatments->contains('id', $activity1->id));
        $this->assertTrue($product->treatments->contains('id', $activity2->id));
    }

    public function test_phytosanitary_product_can_have_null_withdrawal_period(): void
    {
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Sin Plazo',
            'active_ingredient' => 'Ingrediente Activo',
            'registration_number' => 'REG-12345',
            'manufacturer' => 'Fabricante Test',
            'type' => 'insecticide',
            'withdrawal_period_days' => null,
        ]);

        $this->assertNull($product->withdrawal_period_days);
    }

    public function test_phytosanitary_product_fillable_fields(): void
    {
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Completo',
            'active_ingredient' => 'Ingrediente Activo Completo',
            'registration_number' => 'REG-67890',
            'manufacturer' => 'Fabricante Completo',
            'type' => 'fungicide',
            'toxicity_class' => 'II',
            'withdrawal_period_days' => 21,
            'description' => 'Descripción del producto',
        ]);

        $this->assertEquals('Producto Completo', $product->name);
        $this->assertEquals('Ingrediente Activo Completo', $product->active_ingredient);
        $this->assertEquals('REG-67890', $product->registration_number);
        $this->assertEquals('Fabricante Completo', $product->manufacturer);
        $this->assertEquals('fungicide', $product->type);
        $this->assertEquals('II', $product->toxicity_class);
        $this->assertEquals(21, $product->withdrawal_period_days);
        $this->assertEquals('Descripción del producto', $product->description);
    }
}

