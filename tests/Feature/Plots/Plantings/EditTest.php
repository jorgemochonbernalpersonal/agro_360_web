<?php

namespace Tests\Feature\Plots\Plantings;

use App\Models\GrapeVariety;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\User;
use App\Models\WineryViticulturist;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditTest extends TestCase
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

    public function test_viticulturist_can_edit_own_planting(): void
    {
        [$viticulturist, $plot, $planting] = $this->makeViticulturistWithPlanting();

        $variety = GrapeVariety::firstOrCreate(['name' => 'Garnacha'], ['code' => 'GARN', 'color' => 'red']);

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Plantings\Edit::class, ['planting' => $planting])
            ->set('grape_variety_id', $variety->id)
            ->set('area_planted', '3.0')
            ->set('planting_year', 2018)
            ->set('vine_count', 6000)
            ->call('update')
            ->assertRedirect(route('plots.show', $plot));

        $this->assertDatabaseHas('plot_plantings', [
            'id' => $planting->id,
            'grape_variety_id' => $variety->id,
            'planting_year' => 2018,
            'vine_count' => 6000,
        ]);
    }

    public function test_viticulturist_cannot_edit_planting_on_others_plot(): void
    {
        [$viticulturist, $plot, $planting] = $this->makeViticulturistWithPlanting();

        $other = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($other);

        $response = $this->get(route('plots.plantings.edit', $planting));
        $response->assertStatus(403);
    }

    public function test_vine_count_and_density_both_empty_fails_on_update(): void
    {
        [$viticulturist, $plot, $planting] = $this->makeViticulturistWithPlanting();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Plantings\Edit::class, ['planting' => $planting])
            ->set('vine_count', '')
            ->set('density', '')
            ->call('update')
            ->assertHasErrors(['vine_count']);
    }

    public function test_pac_fields_are_updated(): void
    {
        [$viticulturist, $plot, $planting] = $this->makeViticulturistWithPlanting();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Plantings\Edit::class, ['planting' => $planting])
            ->set('vine_count', 4000)
            ->set('planting_authorization', 'AUTH-2024-XYZ')
            ->set('right_type', 'replantacion')
            ->set('designation_of_origin', 'Ribera del Duero DO')
            ->call('update');

        $this->assertDatabaseHas('plot_plantings', [
            'id' => $planting->id,
            'planting_authorization' => 'AUTH-2024-XYZ',
            'right_type' => 'replantacion',
            'designation_of_origin' => 'Ribera del Duero DO',
        ]);
    }

    public function test_area_planted_is_required_on_update(): void
    {
        [$viticulturist, $plot, $planting] = $this->makeViticulturistWithPlanting();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Plantings\Edit::class, ['planting' => $planting])
            ->set('area_planted', '')
            ->call('update')
            ->assertHasErrors(['area_planted']);
    }

    // ── Filtrado de variedades por crop_type según rol ────────────────────────

    public function test_winery_variety_dropdown_contains_only_wine_varieties(): void
    {
        [$winery, $plot, $planting] = $this->makeWineryWithOwnViticulturistAndPlanting();

        GrapeVariety::firstOrCreate(['code' => 'TEMP'], ['name' => 'Tempranillo', 'color' => 'red', 'active' => true, 'crop_type' => 'wine']);
        $olive = GrapeVariety::create(['name' => 'Picual', 'code' => 'PIC', 'color' => 'white', 'active' => true, 'crop_type' => 'olive']);

        $this->actingAs($winery);

        Livewire::test(\App\Livewire\Plots\Plantings\Edit::class, ['planting' => $planting])
            ->assertViewHas('wineryOnly', true)
            ->assertViewHas('grapeVarieties', fn ($v) => ! $v->contains('id', $olive->id));
    }

    public function test_producer_variety_dropdown_contains_all_crop_types(): void
    {
        [$producer, $plot, $planting] = $this->makeProducerWithPlanting();
        $olive = GrapeVariety::create(['name' => 'Picual', 'code' => 'PIC', 'color' => 'white', 'active' => true, 'crop_type' => 'olive']);

        $this->actingAs($producer);

        Livewire::test(\App\Livewire\Plots\Plantings\Edit::class, ['planting' => $planting])
            ->assertViewHas('wineryOnly', false)
            ->assertViewHas('grapeVarieties', fn ($v) => $v->contains('id', $olive->id));
    }

    private function makeViticulturistWithPlanting(): array
    {
        $viticulturist = User::factory()->create([
            'role' => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);

        $plot = Plot::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $variety = GrapeVariety::firstOrCreate(['code' => 'TEMP'], ['name' => 'Tempranillo', 'color' => 'red']);

        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 2.0,
            'status' => 'active',
            'vine_count' => 4000,
        ]);

        $planting->refresh(); // pick up DB defaults (irrigated, active…)

        return [$viticulturist, $plot, $planting];
    }

    // ── Helpers adicionales ───────────────────────────────────────────────────

    private function makeWineryWithOwnViticulturistAndPlanting(): array
    {
        $winery = User::factory()->create(['role' => 'winery', 'email_verified_at' => now()]);
        $viticulturist = User::factory()->create(['role' => 'viticulturist']);
        WineryViticulturist::create([
            'winery_id' => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source' => WineryViticulturist::SOURCE_OWN,
            'assigned_by' => $winery->id,
        ]);
        $plot = Plot::factory()->create(['viticulturist_id' => $viticulturist->id]);
        $variety = GrapeVariety::firstOrCreate(['code' => 'TEMP'], ['name' => 'Tempranillo', 'color' => 'red', 'active' => true, 'crop_type' => 'wine']);
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 2.0,
            'status' => 'active',
        ]);
        $planting->refresh();

        return [$winery, $plot, $planting];
    }

    private function makeProducerWithPlanting(): array
    {
        $producer = User::factory()->create(['role' => 'producer', 'email_verified_at' => now()]);
        $plot = Plot::factory()->create(['viticulturist_id' => $producer->id]);
        $variety = GrapeVariety::firstOrCreate(['code' => 'TEMP'], ['name' => 'Tempranillo', 'color' => 'red', 'active' => true, 'crop_type' => 'wine']);
        $planting = PlotPlanting::create([
            'plot_id' => $plot->id,
            'grape_variety_id' => $variety->id,
            'area_planted' => 1.5,
            'status' => 'active',
        ]);
        $planting->refresh();

        return [$producer, $plot, $planting];
    }
}
