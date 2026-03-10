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

class CreateTest extends TestCase
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

    /**
     * Crea viticultor con winery asignada y una parcela propia.
     */
    private function makeViticulturistWithPlot(): array
    {
        $viticulturist = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $winery = User::factory()->create(['role' => 'winery']);
        WineryViticulturist::create([
            'winery_id'        => $winery->id,
            'viticulturist_id' => $viticulturist->id,
            'source'           => WineryViticulturist::SOURCE_OWN,
            'assigned_by'      => $winery->id,
        ]);

        $plot = Plot::factory()->create(['viticulturist_id' => $viticulturist->id]);

        $variety = GrapeVariety::create(['name' => 'Tempranillo', 'code' => 'TEMP', 'color' => 'red']);

        return [$viticulturist, $plot, $variety];
    }

    public function test_viticulturist_can_create_planting_on_own_plot(): void
    {
        [$viticulturist, $plot, $variety] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Plantings\Create::class, ['plot' => $plot])
            ->set('grape_variety_id', $variety->id)
            ->set('area_planted', '2.5')
            ->set('planting_year', 2015)
            ->set('vine_count', 5000)
            ->set('status', 'active')
            ->call('save')
            ->assertRedirect(route('plots.plantings.index'));

        $this->assertDatabaseHas('plot_plantings', [
            'plot_id'          => $plot->id,
            'grape_variety_id' => $variety->id,
            'planting_year'    => 2015,
        ]);
    }

    public function test_area_planted_is_required(): void
    {
        [$viticulturist, $plot, $variety] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Plantings\Create::class, ['plot' => $plot])
            ->set('area_planted', '')
            ->set('vine_count', 3000)
            ->call('save')
            ->assertHasErrors(['area_planted']);
    }

    public function test_vine_count_and_density_both_empty_fails(): void
    {
        [$viticulturist, $plot, $variety] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Plantings\Create::class, ['plot' => $plot])
            ->set('grape_variety_id', $variety->id)
            ->set('area_planted', '1.0')
            ->set('vine_count', '')
            ->set('density', '')
            ->call('save')
            ->assertHasErrors(['vine_count']);

        $this->assertDatabaseCount('plot_plantings', 0);
    }

    public function test_vine_count_alone_passes_density_check(): void
    {
        [$viticulturist, $plot, $variety] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Plantings\Create::class, ['plot' => $plot])
            ->set('grape_variety_id', $variety->id)
            ->set('area_planted', '1.0')
            ->set('vine_count', 3000)
            ->set('density', '')
            ->call('save')
            ->assertRedirect(route('plots.plantings.index'));

        $this->assertDatabaseHas('plot_plantings', [
            'plot_id'   => $plot->id,
            'vine_count' => 3000,
        ]);
    }

    public function test_density_alone_passes_density_check(): void
    {
        [$viticulturist, $plot, $variety] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Plantings\Create::class, ['plot' => $plot])
            ->set('grape_variety_id', $variety->id)
            ->set('area_planted', '1.0')
            ->set('vine_count', '')
            ->set('density', 3000)
            ->call('save')
            ->assertRedirect(route('plots.plantings.index'));

        $this->assertDatabaseHas('plot_plantings', [
            'plot_id' => $plot->id,
            'density' => 3000,
        ]);
    }

    public function test_pac_fields_are_saved(): void
    {
        [$viticulturist, $plot, $variety] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Plantings\Create::class, ['plot' => $plot])
            ->set('grape_variety_id', $variety->id)
            ->set('area_planted', '1.5')
            ->set('vine_count', 4000)
            ->set('planting_authorization', 'AUTH-2024-001')
            ->set('authorization_date', '2024-01-15')
            ->set('right_type', 'nueva')
            ->set('designation_of_origin', 'Rioja DOCa')
            ->call('save');

        $this->assertDatabaseHas('plot_plantings', [
            'plot_id'                 => $plot->id,
            'planting_authorization'  => 'AUTH-2024-001',
            'right_type'              => 'nueva',
            'designation_of_origin'   => 'Rioja DOCa',
        ]);
    }

    public function test_invalid_right_type_fails_validation(): void
    {
        [$viticulturist, $plot, $variety] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Plantings\Create::class, ['plot' => $plot])
            ->set('area_planted', '1.0')
            ->set('vine_count', 3000)
            ->set('right_type', 'invalido')
            ->call('save')
            ->assertHasErrors(['right_type']);
    }

    public function test_viticulturist_cannot_access_another_viticulturists_plot(): void
    {
        [$viticulturist, $plot, $variety] = $this->makeViticulturistWithPlot();

        $other = User::factory()->create([
            'role'              => 'viticulturist',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($other);

        $response = $this->get(route('plots.plantings.create', $plot));
        $response->assertStatus(403);
    }
}
