<?php

namespace Tests\Feature\Plots;

use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use App\Models\SigpacUse;
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

        $ac         = AutonomousCommunity::first();
        $province   = Province::where('autonomous_community_id', $ac->id)->first();
        $municipality = Municipality::where('province_id', $province->id)->first();

        $plot = Plot::factory()->create([
            'viticulturist_id'        => $viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id'             => $province->id,
            'municipality_id'         => $municipality->id,
        ]);

        $plot->refresh(); // pick up DB defaults (ndvi_alert_threshold, alert_email_enabled, tenure_regime…)

        $sigpacUse = SigpacUse::create(['code' => 'VI', 'description' => 'Viñedo']);
        $plot->sigpacUses()->sync([$sigpacUse->id]);

        return [$viticulturist, $plot, $ac, $province, $municipality];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // tenure_regime
    // ──────────────────────────────────────────────────────────────────────────

    public function test_tenure_regime_is_saved_on_edit(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('tenure_regime', 'arrendamiento')
            ->call('update')
            ->assertHasNoErrors(['tenure_regime']);

        $this->assertDatabaseHas('plots', [
            'id'             => $plot->id,
            'tenure_regime'  => 'arrendamiento',
        ]);
    }

    public function test_invalid_tenure_regime_fails_validation(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('tenure_regime', 'herencia')   // valor no permitido
            ->call('update')
            ->assertHasErrors(['tenure_regime']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // soil_type
    // ──────────────────────────────────────────────────────────────────────────

    public function test_soil_type_is_saved_on_edit(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('soil_type', 'franco-arcilloso')
            ->call('update');

        $this->assertDatabaseHas('plots', [
            'id'        => $plot->id,
            'soil_type' => 'franco-arcilloso',
        ]);
    }

    public function test_invalid_soil_type_fails_validation(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('soil_type', 'volcanico')   // valor no permitido
            ->call('update')
            ->assertHasErrors(['soil_type']);
    }

    public function test_soil_type_can_be_left_empty(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('soil_type', '')
            ->call('update')
            ->assertHasNoErrors(['soil_type']);

        $this->assertDatabaseHas('plots', [
            'id'        => $plot->id,
            'soil_type' => null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // orientation
    // ──────────────────────────────────────────────────────────────────────────

    public function test_orientation_is_saved_on_edit(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('orientation', 'SE')
            ->call('update');

        $this->assertDatabaseHas('plots', [
            'id'          => $plot->id,
            'orientation' => 'SE',
        ]);
    }

    public function test_invalid_orientation_fails_validation(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
            ->set('orientation', 'SUR')   // valor no permitido (debe ser 'S')
            ->call('update')
            ->assertHasErrors(['orientation']);
    }

    public function test_all_valid_orientations_pass(): void
    {
        [$viticulturist, $plot] = $this->makeViticulturistWithPlot();

        $this->actingAs($viticulturist);

        foreach (['N', 'NE', 'E', 'SE', 'S', 'SO', 'O', 'NO'] as $orientation) {
            Livewire::test(\App\Livewire\Plots\Edit::class, ['plot' => $plot])
                ->set('orientation', $orientation)
                ->call('update')
                ->assertHasNoErrors(['orientation']);
        }
    }
}
