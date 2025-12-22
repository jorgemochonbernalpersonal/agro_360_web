<?php

namespace Tests\Unit\Models;

use App\Models\AgriculturalActivity;
use App\Models\Machinery;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\GrapeVariety;
use App\Models\User;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineryTest extends TestCase
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

    public function test_machinery_belongs_to_viticulturist(): void
    {
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);

        $machinery = Machinery::create([
            'name' => 'Tractor Test',
            'type' => 'Tractor',
            'viticulturist_id' => $viticulturist->id,
        ]);

        $this->assertEquals($viticulturist->id, $machinery->viticulturist->id);
    }

    public function test_scopes_filter_correctly(): void
    {
        $viticulturist1 = User::factory()->create(['role' => 'viticulturist']);
        $viticulturist2 = User::factory()->create(['role' => 'viticulturist']);

        $machinery1 = Machinery::create([
            'name' => 'Tractor A',
            'type' => 'Tractor',
            'viticulturist_id' => $viticulturist1->id,
            'active' => true,
        ]);

        $machinery2 = Machinery::create([
            'name' => 'Atomizador B',
            'type' => 'Atomizador',
            'viticulturist_id' => $viticulturist1->id,
            'active' => false,
        ]);

        $machinery3 = Machinery::create([
            'name' => 'Tractor C',
            'type' => 'Tractor',
            'viticulturist_id' => $viticulturist2->id,
            'active' => true,
        ]);

        $this->assertEquals(
            [$machinery1->id, $machinery3->id],
            Machinery::active()->pluck('id')->sort()->values()->all()
        );

        $this->assertEquals(
            [$machinery1->id, $machinery2->id],
            Machinery::forViticulturist($viticulturist1->id)->pluck('id')->sort()->values()->all()
        );

        $this->assertEquals(
            [$machinery1->id, $machinery3->id],
            Machinery::ofType('Tractor')->pluck('id')->sort()->values()->all()
        );
    }

    public function test_activities_count_attribute_returns_correct_value(): void
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

        $machinery = Machinery::create([
            'name' => 'Tractor Test',
            'type' => 'Tractor',
            'viticulturist_id' => $viticulturist->id,
        ]);

        AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => null,
            'activity_type' => 'phytosanitary',
            'activity_date' => now(),
            'machinery_id' => $machinery->id,
        ]);

        AgriculturalActivity::create([
            'plot_id' => $plot->id,
            'plot_planting_id' => $planting->id,
            'viticulturist_id' => $viticulturist->id,
            'campaign_id' => null,
            'activity_type' => 'fertilization',
            'activity_date' => now(),
            'machinery_id' => $machinery->id,
        ]);

        $this->assertEquals(2, $machinery->activities_count);
    }
}


