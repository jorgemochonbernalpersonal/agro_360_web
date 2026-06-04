<?php

namespace Tests\Feature\Plots;

use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Orientation;
use App\Models\Plot;
use App\Models\Province;
use App\Models\SigpacUse;
use App\Models\SoilType;
use App\Models\User;
use App\Models\WineryViticulturist;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlotFieldsTest extends TestCase
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

    // ──────────────────────────────────────────────────────────────────────────
    // soil_type_id
    // ──────────────────────────────────────────────────────────────────────────

    public function test_soil_type_is_saved_on_edit(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $soilType = SoilType::create(['name' => 'Franco-arcilloso', 'active' => true]);

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('soil_type_id', $soilType->id)
            ->call('update');

        $this->assertDatabaseHas('plots', [
            'id' => $plot->id,
            'soil_type_id' => $soilType->id,
        ]);
    }

    public function test_invalid_soil_type_fails_validation(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('soil_type_id', 99999)   // ID que no existe
            ->call('update')
            ->assertHasErrors(['soil_type_id']);
    }

    public function test_soil_type_can_be_left_empty(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('soil_type_id', '')
            ->call('update')
            ->assertHasNoErrors(['soil_type_id']);

        $this->assertDatabaseHas('plots', [
            'id' => $plot->id,
            'soil_type_id' => null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // orientation_id
    // ──────────────────────────────────────────────────────────────────────────

    public function test_orientation_is_saved_on_edit(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $orientation = Orientation::create(['name' => 'Sureste', 'abbreviation' => 'SE', 'active' => true]);

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('orientation_id', $orientation->id)
            ->call('update');

        $this->assertDatabaseHas('plots', [
            'id' => $plot->id,
            'orientation_id' => $orientation->id,
        ]);
    }

    public function test_invalid_orientation_fails_validation(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('orientation_id', 99999)   // ID que no existe
            ->call('update')
            ->assertHasErrors(['orientation_id']);
    }

    public function test_orientation_can_be_left_empty(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('orientation_id', '')
            ->call('update')
            ->assertHasNoErrors(['orientation_id']);

        $this->assertDatabaseHas('plots', [
            'id' => $plot->id,
            'orientation_id' => null,
        ]);
    }

    private function makeViticulturistWithPlot(): array
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

        $ac = AutonomousCommunity::first();
        $province = Province::where('autonomous_community_id', $ac->id)->first();
        $municipality = Municipality::where('province_id', $province->id)->first();

        $plot = Plot::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id' => $province->id,
            'municipality_id' => $municipality->id,
        ]);

        $plot->refresh();

        $sigpacUse = SigpacUse::firstOrCreate(['code' => 'VI'], ['description' => 'Viñedo']);
        $plot->sigpacUses()->sync([$sigpacUse->id]);

        return [$viticulturist, $plot, $ac, $province, $municipality];
    }
}
