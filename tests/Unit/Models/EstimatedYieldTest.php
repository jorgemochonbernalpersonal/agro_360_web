<?php

namespace Tests\Unit\Models;

use App\Models\EstimatedYield;
use App\Models\PlotPlanting;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Plot;
use App\Models\Harvest;
use App\Models\AgriculturalActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;

class EstimatedYieldTest extends TestCase
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

    public function test_estimated_yield_belongs_to_plot_planting(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'estimation_date' => now(),
        ]);

        $this->assertEquals($planting->id, $yield->plotPlanting->id);
    }

    public function test_estimated_yield_belongs_to_campaign(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'estimation_date' => now(),
        ]);

        $this->assertEquals($campaign->id, $yield->campaign->id);
    }

    public function test_estimated_yield_belongs_to_estimator(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $estimator = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_by' => $estimator->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'estimation_date' => now(),
        ]);

        $this->assertEquals($estimator->id, $yield->estimator->id);
    }

    public function test_variance_percentage_is_calculated_automatically(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_total_yield' => 1000.0,
            'actual_total_yield' => 1100.0, // 10% más
            'estimation_date' => now(),
        ]);

        // Debe calcular: ((1100 - 1000) / 1000) * 100 = 10%
        $this->assertEquals(10.0, $yield->variance_percentage);
    }

    public function test_variance_percentage_is_negative_when_actual_is_less(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_total_yield' => 1000.0,
            'actual_total_yield' => 900.0, // 10% menos
            'estimation_date' => now(),
        ]);

        // Debe calcular: ((900 - 1000) / 1000) * 100 = -10%
        $this->assertEquals(-10.0, $yield->variance_percentage);
    }

    public function test_variance_percentage_is_null_when_no_actual_yield(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_total_yield' => 1000.0,
            'actual_total_yield' => null,
            'estimation_date' => now(),
        ]);

        $this->assertNull($yield->variance_percentage);
    }

    public function test_variance_percentage_is_null_when_estimated_is_zero(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_total_yield' => 0.0,
            'actual_total_yield' => 1000.0,
            'estimation_date' => now(),
        ]);

        // No debe calcular si estimated es 0
        $this->assertNull($yield->variance_percentage);
    }

    public function test_scope_for_planting_filters_by_planting(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $planting1 = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $planting2 = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield1 = EstimatedYield::create([
            'plot_planting_id' => $planting1->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'estimation_date' => now(),
        ]);

        $yield2 = EstimatedYield::create([
            'plot_planting_id' => $planting2->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 6000.0,
            'estimated_total_yield' => 6000.0,
            'estimation_date' => now(),
        ]);

        $results = EstimatedYield::forPlanting($planting1->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($yield1->id, $results->first()->id);
    }

    public function test_scope_for_campaign_filters_by_campaign(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign1 = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        $campaign2 = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield1 = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign1->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'estimation_date' => now(),
        ]);

        $yield2 = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign2->id,
            'estimated_yield_per_hectare' => 6000.0,
            'estimated_total_yield' => 6000.0,
            'estimation_date' => now(),
        ]);

        $results = EstimatedYield::forCampaign($campaign1->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($yield1->id, $results->first()->id);
    }

    public function test_scope_confirmed_filters_confirmed_estimations(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        // Crear diferentes plantings para evitar constraint único
        $planting1 = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);
        
        $planting2 = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);
        
        $planting3 = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $confirmed1 = EstimatedYield::create([
            'plot_planting_id' => $planting1->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'estimation_date' => now(),
            'status' => 'confirmed',
        ]);

        $confirmed2 = EstimatedYield::create([
            'plot_planting_id' => $planting2->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 6000.0,
            'estimated_total_yield' => 6000.0,
            'estimation_date' => now(),
            'status' => 'confirmed',
        ]);

        $draft = EstimatedYield::create([
            'plot_planting_id' => $planting3->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 7000.0,
            'estimated_total_yield' => 7000.0,
            'estimation_date' => now(),
            'status' => 'draft',
        ]);

        $results = EstimatedYield::confirmed()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $confirmed1->id));
        $this->assertTrue($results->contains('id', $confirmed2->id));
        $this->assertFalse($results->contains('id', $draft->id));
    }

    public function test_scope_draft_filters_draft_estimations(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        // Crear diferentes plantings para evitar constraint único
        $planting1 = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);
        
        $planting2 = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $draft1 = EstimatedYield::create([
            'plot_planting_id' => $planting1->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'estimation_date' => now(),
            'status' => 'draft',
        ]);

        $confirmed = EstimatedYield::create([
            'plot_planting_id' => $planting2->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 6000.0,
            'estimated_total_yield' => 6000.0,
            'estimation_date' => now(),
            'status' => 'confirmed',
        ]);

        $results = EstimatedYield::draft()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($draft1->id, $results->first()->id);
    }

    public function test_is_confirmed_returns_true_when_status_is_confirmed(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'estimation_date' => now(),
            'status' => 'confirmed',
        ]);

        $this->assertTrue($yield->isConfirmed());
    }

    public function test_is_confirmed_returns_false_when_status_is_not_confirmed(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'estimation_date' => now(),
            'status' => 'draft',
        ]);

        $this->assertFalse($yield->isConfirmed());
    }

    public function test_has_actual_yield_returns_true_when_actual_total_yield_exists(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'actual_total_yield' => 5500.0,
            'estimation_date' => now(),
        ]);

        $this->assertTrue($yield->hasActualYield());
    }

    public function test_has_actual_yield_returns_false_when_actual_total_yield_is_null(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'actual_total_yield' => null,
            'estimation_date' => now(),
        ]);

        $this->assertFalse($yield->hasActualYield());
    }

    public function test_get_absolute_variance_returns_absolute_difference(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_total_yield' => 1000.0,
            'actual_total_yield' => 1100.0,
            'estimation_date' => now(),
        ]);

        // Debe retornar: abs(1100 - 1000) = 100
        $this->assertEquals(100.0, $yield->getAbsoluteVariance());
    }

    public function test_get_absolute_variance_returns_absolute_difference_for_negative_variance(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_total_yield' => 1000.0,
            'actual_total_yield' => 900.0,
            'estimation_date' => now(),
        ]);

        // Debe retornar: abs(900 - 1000) = 100 (siempre positivo)
        $this->assertEquals(100.0, $yield->getAbsoluteVariance());
    }

    public function test_get_absolute_variance_returns_null_when_no_actual_yield(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_total_yield' => 1000.0,
            'actual_total_yield' => null,
            'estimation_date' => now(),
        ]);

        $this->assertNull($yield->getAbsoluteVariance());
    }

    public function test_get_absolute_variance_returns_null_when_no_estimated_yield(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_total_yield' => null,
            'actual_total_yield' => 1000.0,
            'estimation_date' => now(),
        ]);

        $this->assertNull($yield->getAbsoluteVariance());
    }

    public function test_decimal_fields_are_cast_to_decimal(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 5000.123,
            'estimated_total_yield' => 5000.456,
            'actual_yield_per_hectare' => 5500.789,
            'actual_total_yield' => 5500.012,
            'variance_percentage' => 10.50,
            'estimation_date' => now(),
        ]);

        // Los campos decimal:3 devuelven strings en Laravel
        $this->assertIsString($yield->estimated_yield_per_hectare);
        $this->assertIsString($yield->estimated_total_yield);
        $this->assertIsString($yield->actual_yield_per_hectare);
        $this->assertIsString($yield->actual_total_yield);
        $this->assertIsString($yield->variance_percentage);
    }

    public function test_estimation_date_is_cast_to_date(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $campaign = Campaign::factory()->create(['viticulturist_id' => $viticulturist->id]);
        
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        $yield = EstimatedYield::create([
            'plot_planting_id' => $planting->id,
            'campaign_id' => $campaign->id,
            'estimated_yield_per_hectare' => 5000.0,
            'estimated_total_yield' => 5000.0,
            'estimation_date' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $yield->estimation_date);
    }
}

