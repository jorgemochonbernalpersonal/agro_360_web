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

class GrapeVarietyTest extends TestCase
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

    public function test_variety_has_many_plantings(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();

        $variety = GrapeVariety::create([
            'name' => 'Tempranillo',
            'code' => 'TEMP',
            'color' => 'red',
        ]);

        PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 0.5,
            'status' => 'removed',
        ]);

        $this->assertCount(2, $variety->plantings);
    }

    public function test_scopes_active_and_by_color_filter_correctly(): void
    {
        $redActive = GrapeVariety::create([
            'name' => 'Tempranillo',
            'code' => 'TEMP',
            'color' => 'red',
            'active' => true,
        ]);

        $redInactive = GrapeVariety::create([
            'name' => 'Garnacha',
            'code' => 'GARN',
            'color' => 'red',
            'active' => false,
        ]);

        $whiteActive = GrapeVariety::create([
            'name' => 'Verdejo',
            'code' => 'VER',
            'color' => 'white',
            'active' => true,
        ]);

        $this->assertEquals(
            [$redActive->id, $whiteActive->id],
            GrapeVariety::active()->pluck('id')->sort()->values()->all()
        );

        $this->assertEquals(
            [$redActive->id, $redInactive->id],
            GrapeVariety::byColor('red')->pluck('id')->sort()->values()->all()
        );
    }
}


