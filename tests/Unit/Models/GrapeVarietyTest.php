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

        $variety = GrapeVariety::firstOrCreate(
            ['code' => 'TEMP'],
            ['name' => 'Tempranillo', 'color' => 'red']
        );

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
        // Clear seeder data so scope assertions can check exact results.
        GrapeVariety::query()->delete();

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

    // ── crop_type ─────────────────────────────────────────────────────────────

    public function test_crop_type_defaults_to_wine(): void
    {
        GrapeVariety::query()->delete();

        $variety = GrapeVariety::create([
            'name' => 'VariedadTest',
            'code' => 'VT01',
            'color' => 'red',
            'active' => true,
        ]);

        $this->assertEquals('wine', $variety->fresh()->crop_type);
    }

    public function test_scope_wine_returns_only_wine_varieties(): void
    {
        GrapeVariety::query()->delete();

        $wine = GrapeVariety::create(['name' => 'UvaVino',   'code' => 'UV', 'color' => 'red',  'active' => true, 'crop_type' => 'wine']);
        $olive = GrapeVariety::create(['name' => 'Aceituna',  'code' => 'AC', 'color' => 'white', 'active' => true, 'crop_type' => 'olive']);
        $other = GrapeVariety::create(['name' => 'OtroCult',  'code' => 'OC', 'color' => 'rose', 'active' => true, 'crop_type' => 'other']);

        $results = GrapeVariety::wine()->pluck('id');

        $this->assertContains($wine->id, $results->all());
        $this->assertNotContains($olive->id, $results->all());
        $this->assertNotContains($other->id, $results->all());
    }

    public function test_scope_by_crop_type_filters_by_type(): void
    {
        GrapeVariety::query()->delete();

        $wine = GrapeVariety::create(['name' => 'UvaVino',  'code' => 'UV', 'color' => 'red',  'active' => true, 'crop_type' => 'wine']);
        $olive = GrapeVariety::create(['name' => 'Aceituna', 'code' => 'AC', 'color' => 'white', 'active' => true, 'crop_type' => 'olive']);

        $oliveResults = GrapeVariety::byCropType('olive')->pluck('id');

        $this->assertContains($olive->id, $oliveResults->all());
        $this->assertNotContains($wine->id, $oliveResults->all());
    }

    public function test_crop_type_label_attribute_returns_human_label(): void
    {
        GrapeVariety::query()->delete();

        $wine = GrapeVariety::create(['name' => 'UvaVino',  'code' => 'UV', 'color' => 'red',  'active' => true, 'crop_type' => 'wine']);
        $olive = GrapeVariety::create(['name' => 'Aceituna', 'code' => 'AC', 'color' => 'white', 'active' => true, 'crop_type' => 'olive']);
        $other = GrapeVariety::create(['name' => 'OtroCult', 'code' => 'OC', 'color' => 'rose', 'active' => true, 'crop_type' => 'other']);

        $this->assertEquals('Uva (vino)', $wine->crop_type_label);
        $this->assertEquals('Aceituna (aceite)', $olive->crop_type_label);
        $this->assertEquals('Otro cultivo', $other->crop_type_label);
    }

    public function test_crop_type_icon_attribute_returns_correct_icon(): void
    {
        GrapeVariety::query()->delete();

        $wine = GrapeVariety::create(['name' => 'UvaVino',  'code' => 'UV', 'color' => 'red',  'active' => true, 'crop_type' => 'wine']);
        $olive = GrapeVariety::create(['name' => 'Aceituna', 'code' => 'AC', 'color' => 'white', 'active' => true, 'crop_type' => 'olive']);
        $other = GrapeVariety::create(['name' => 'OtroCult', 'code' => 'OC', 'color' => 'rose', 'active' => true, 'crop_type' => 'other']);

        $this->assertEquals('scissors', $wine->crop_type_icon);
        $this->assertEquals('sun', $olive->crop_type_icon);
        $this->assertEquals('sparkles', $other->crop_type_icon);
    }
}
