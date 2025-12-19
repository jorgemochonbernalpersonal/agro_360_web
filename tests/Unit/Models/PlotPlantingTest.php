<?php

namespace Tests\Unit\Models;

use App\Models\GrapeVariety;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlotPlantingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed de localización requerido por los factories de Plot
        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    public function test_plot_planting_belongs_to_plot_and_grape_variety(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();

        $variety = GrapeVariety::create([
            'name' => 'Tempranillo',
            'code' => 'TEMP',
            'color' => 'red',
        ]);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 1.5,
            'planting_year' => 2020,
            'status' => 'active',
        ]);

        $this->assertEquals($plot->id, $planting->plot->id);
        $this->assertEquals($variety->id, $planting->grapeVariety->id);
    }

    public function test_scopes_active_and_irrigated_filter_correctly(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $variety = GrapeVariety::create([
            'name' => 'Verdejo',
            'code' => 'VER',
            'color' => 'white',
        ]);

        $activeIrrigated = PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 2.0,
            'status' => 'active',
            'irrigated' => true,
        ]);

        $activeNonIrrigated = PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 1.0,
            'status' => 'active',
            'irrigated' => false,
        ]);

        $removed = PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 0.5,
            'status' => 'removed',
            'irrigated' => true,
        ]);

        $this->assertEquals(
            [$activeIrrigated->id, $activeNonIrrigated->id],
            PlotPlanting::active()->pluck('id')->sort()->values()->all()
        );

        $this->assertEquals(
            [$activeIrrigated->id, $removed->id],
            PlotPlanting::irrigated()->pluck('id')->sort()->values()->all()
        );
    }

    public function test_plot_relationship_returns_all_plantings_for_plot(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $variety1 = GrapeVariety::create(['name' => 'Tempranillo', 'code' => 'TEMP', 'color' => 'red']);
        $variety2 = GrapeVariety::create(['name' => 'Garnacha', 'code' => 'GARN', 'color' => 'red']);

        PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety1->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety2->id,
            'area_planted' => 0.5,
            'status' => 'active',
        ]);

        $this->assertCount(2, $plot->plantings);
    }
}


