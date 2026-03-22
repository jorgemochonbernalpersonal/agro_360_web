<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Fertilization;

use App\Livewire\Viticulturist\DigitalNotebook\CreateFertilization;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Fertilization;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    private function makePlot($viticulturist): Plot
    {
        $ac   = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        return Plot::factory()->create([
            'viticulturist_id'        => $viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id'             => $prov->id,
            'municipality_id'         => $muni->id,
            'active'                  => true,
        ]);
    }

    private function makeCampaign($viticulturist): Campaign
    {
        return Campaign::factory()->active()->create([
            'viticulturist_id' => $viticulturist->id,
            'year'             => now()->year,
        ]);
    }

    // ── mount ──────────────────────────────────────────────────────────────────

    public function test_component_renders(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateFertilization::class)
            ->assertOk();
    }

    public function test_mount_sets_today_as_activity_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateFertilization::class)
            ->assertSet('activity_date', now()->format('Y-m-d'));
    }

    // ── validaciones básicas ──────────────────────────────────────────────────

    public function test_validates_fertilizer_type_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateFertilization::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('fertilizer_type', '')
            ->set('quantity', 200)
            ->set('area_applied', 1.5)
            ->set('nitrogen_uf', 150)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['fertilizer_type']);
    }

    public function test_validates_quantity_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateFertilization::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('fertilizer_type', 'Mineral')
            ->set('quantity', '')
            ->set('area_applied', 1.5)
            ->set('nitrogen_uf', 150)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['quantity']);
    }

    public function test_validates_area_applied_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateFertilization::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('fertilizer_type', 'Mineral')
            ->set('quantity', 200)
            ->set('area_applied', '')
            ->set('nitrogen_uf', 150)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['area_applied']);
    }

    public function test_validates_phenological_stage_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateFertilization::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', '')
            ->set('fertilizer_type', 'Mineral')
            ->set('quantity', 200)
            ->set('area_applied', 1.5)
            ->set('nitrogen_uf', 150)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['phenological_stage']);
    }

    // ── PAC: NPK al menos uno requerido ───────────────────────────────────────

    public function test_validates_at_least_one_npk_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateFertilization::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('fertilizer_type', 'Mineral')
            ->set('quantity', 200)
            ->set('area_applied', 1.5)
            ->set('nitrogen_uf', '')
            ->set('phosphorus_uf', '')
            ->set('potassium_uf', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['nitrogen_uf']);
    }

    public function test_validates_worktype_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateFertilization::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('fertilizer_type', 'Mineral')
            ->set('quantity', 200)
            ->set('area_applied', 1.5)
            ->set('nitrogen_uf', 150)
            ->set('workType', '')
            ->call('save')
            ->assertHasErrors(['workType']);
    }

    // ── PAC: fertilizante orgánico requiere manure_type y burial_date ─────────

    public function test_organic_fertilizer_requires_manure_type_and_burial_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateFertilization::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('fertilizer_type', 'Fertilizante orgánico')
            ->set('quantity', 3000)
            ->set('area_applied', 1.5)
            ->set('nitrogen_uf', 50)
            ->set('manure_type', '')
            ->set('burial_date', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['manure_type', 'burial_date']);
    }

    // ── guardado completo ──────────────────────────────────────────────────────

    public function test_can_save_mineral_fertilization(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateFertilization::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('fertilizer_type', 'Mineral')
            ->set('fertilizer_name', 'NPK 15-15-15')
            ->set('quantity', 200)
            ->set('area_applied', 2.5)
            ->set('npk_ratio', '15-15-15')
            ->set('application_method', 'aplicación al suelo')
            ->set('nitrogen_uf', 150)
            ->set('phosphorus_uf', 100)
            ->set('potassium_uf', 80)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.fertilization.index'));

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type'    => 'fertilization',
        ])->first();

        $this->assertDatabaseHas('fertilizations', [
            'activity_id'     => $activity->id,
            'fertilizer_type' => 'Mineral',
            'fertilizer_name' => 'NPK 15-15-15',
        ]);
    }

    public function test_can_save_organic_fertilization_with_all_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        $burialDate = now()->subDays(5)->format('Y-m-d');

        Livewire::test(CreateFertilization::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Reposo invernal')
            ->set('fertilizer_type', 'Fertilizante orgánico')
            ->set('fertilizer_name', 'Compost de bodega')
            ->set('quantity', 5000)
            ->set('area_applied', 2.0)
            ->set('nitrogen_uf', 80)
            ->set('manure_type', 'Estiércol vacuno')
            ->set('burial_date', $burialDate)
            ->set('emission_reduction_method', 'Enterrado en menos de 24h')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type'    => 'fertilization',
        ])->first();

        $this->assertDatabaseHas('fertilizations', [
            'activity_id'  => $activity->id,
            'manure_type'  => 'Estiércol vacuno',
        ]);
    }

    // ── NPK casts decimal:3 ────────────────────────────────────────────────────

    public function test_npk_uf_values_stored_with_decimal_precision(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateFertilization::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('fertilizer_type', 'Mineral')
            ->set('quantity', 200)
            ->set('area_applied', 2.5)
            ->set('nitrogen_uf', 123.5)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type'    => 'fertilization',
        ])->first();

        $fertilization = Fertilization::where('activity_id', $activity->id)->first();
        $this->assertEquals('123.500', $fertilization->nitrogen_uf);
    }

    // ── autorización ──────────────────────────────────────────────────────────

    public function test_policy_denies_create_for_other_viticulturist_plot(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $plot          = $this->makePlot($viticulturist);

        $this->assertFalse($other->can('create-activity', $plot));
    }
}
