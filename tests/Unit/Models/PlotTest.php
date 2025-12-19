<?php

namespace Tests\Unit\Models;

use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use App\Models\WineryViticulturist;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlotTest extends TestCase
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

    public function test_plot_belongs_to_viticulturist_and_winery(): void
    {
        $winery = User::factory()->create(['role' => 'winery']);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $plot = Plot::factory()
            ->state([
                'winery_id' => $winery->id,
                'viticulturist_id' => $viticulturist->id,
            ])
            ->create();

        $this->assertEquals($winery->id, $plot->winery->id);
        $this->assertEquals($viticulturist->id, $plot->viticulturist->id);
    }

    public function test_plantings_relationship_returns_all_plantings(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        $plot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();

        PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 1.0,
            'status' => 'active',
        ]);

        PlotPlanting::create([
            'plot_id' => $plot->id,
            'area_planted' => 0.5,
            'status' => 'removed',
        ]);

        $this->assertCount(2, $plot->plantings);
    }

    public function test_scope_for_viticulturist_includes_own_and_created_viticulturists_plots(): void
    {
        $parent = User::factory()->create(['role' => 'viticulturist']);
        $child = User::factory()->create(['role' => 'viticulturist']);
        $other = User::factory()->create(['role' => 'viticulturist']);

        // Relación de viticultor creado
        WineryViticulturist::create([
            'viticulturist_id' => $child->id,
            'parent_viticulturist_id' => $parent->id,
            'source' => WineryViticulturist::SOURCE_VITICULTURIST,
            'assigned_by' => $parent->id,
        ]);

        $ownPlot = Plot::factory()->state(['viticulturist_id' => $parent->id])->create();
        $childPlot = Plot::factory()->state(['viticulturist_id' => $child->id])->create();
        $otherPlot = Plot::factory()->state(['viticulturist_id' => $other->id])->create();

        $visiblePlots = Plot::forViticulturist($parent)->pluck('id')->sort()->values()->all();

        $this->assertEqualsCanonicalizing(
            [$ownPlot->id, $childPlot->id],
            $visiblePlots
        );
        $this->assertNotContains($otherPlot->id, $visiblePlots);
    }

    public function test_scope_for_user_with_viticulturist_delegates_to_for_viticulturist(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $ownPlot = Plot::factory()->state(['viticulturist_id' => $viticulturist->id])->create();
        $otherPlot = Plot::factory()->create(); // otro viticultor

        $visibleIds = Plot::forUser($viticulturist)->pluck('id')->all();

        $this->assertContains($ownPlot->id, $visibleIds);
        $this->assertNotContains($otherPlot->id, $visibleIds);
    }
}


