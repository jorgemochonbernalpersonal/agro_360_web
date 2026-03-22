<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Irrigation;

use App\Livewire\Viticulturist\DigitalNotebook\CreateIrrigation;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Irrigation;
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

    // ── mount: componente renderiza ────────────────────────────────────────────

    public function test_component_renders(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateIrrigation::class)
            ->assertOk();
    }

    public function test_mount_sets_today_as_activity_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateIrrigation::class)
            ->assertSet('activity_date', now()->format('Y-m-d'));
    }

    // ── validaciones campos PAC obligatorios ──────────────────────────────────

    public function test_validates_pac_fields_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateIrrigation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('water_volume', 500)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            // PAC fields vacíos
            ->set('water_source', '')
            ->set('water_concession', '')
            ->set('flow_rate', '')
            ->call('save')
            ->assertHasErrors(['water_source', 'water_concession', 'flow_rate']);
    }

    public function test_validates_water_volume_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateIrrigation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('water_volume', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->set('water_source', 'Pozo legalizado')
            ->set('water_concession', 'EXP-2024/001')
            ->set('flow_rate', 2000)
            ->call('save')
            ->assertHasErrors(['water_volume']);
    }

    public function test_validates_phenological_stage_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateIrrigation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', '')
            ->set('water_volume', 500)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->set('water_source', 'Pozo legalizado')
            ->set('water_concession', 'EXP-2024/001')
            ->set('flow_rate', 2000)
            ->call('save')
            ->assertHasErrors(['phenological_stage']);
    }

    public function test_validates_worktype_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateIrrigation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('water_volume', 500)
            ->set('workType', '')
            ->set('water_source', 'Pozo legalizado')
            ->set('water_concession', 'EXP-2024/001')
            ->set('flow_rate', 2000)
            ->call('save')
            ->assertHasErrors(['workType']);
    }

    // ── guardado completo ──────────────────────────────────────────────────────

    public function test_can_save_with_all_pac_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateIrrigation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('water_volume', 750.5)
            ->set('irrigation_method', 'goteo')
            ->set('duration_minutes', 90)
            ->set('water_source', 'Pozo legalizado')
            ->set('water_concession', 'EXP-2024/001')
            ->set('flow_rate', 2500.0)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.irrigation.index'));

        $this->assertDatabaseHas('agricultural_activities', [
            'viticulturist_id' => $viticulturist->id,
            'activity_type'    => 'irrigation',
            'plot_id'          => $plot->id,
        ]);

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type'    => 'irrigation',
        ])->first();

        $this->assertDatabaseHas('irrigations', [
            'activity_id'      => $activity->id,
            'irrigation_method' => 'goteo',
            'duration_minutes' => 90,
            'water_source'     => 'Pozo legalizado',
            'water_concession' => 'EXP-2024/001',
        ]);
    }

    // ── campos opcionales: humedad de suelo ───────────────────────────────────

    public function test_can_save_with_soil_moisture_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateIrrigation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Floración')
            ->set('water_volume', 500)
            ->set('soil_moisture_before', 22.5)
            ->set('soil_moisture_after', 48.0)
            ->set('water_source', 'Comunidad de regantes')
            ->set('water_concession', 'CR-2023/555')
            ->set('flow_rate', 1800)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type'    => 'irrigation',
        ])->first();

        $this->assertDatabaseHas('irrigations', [
            'activity_id' => $activity->id,
        ]);

        $irrigation = Irrigation::where('activity_id', $activity->id)->first();
        $this->assertEquals('22.50', $irrigation->soil_moisture_before);
        $this->assertEquals('48.00', $irrigation->soil_moisture_after);
    }

    // ── validación soil moisture rango 0-100 ──────────────────────────────────

    public function test_soil_moisture_must_be_between_0_and_100(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateIrrigation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('water_volume', 500)
            ->set('soil_moisture_before', 150)
            ->set('water_source', 'Pozo legalizado')
            ->set('water_concession', 'EXP/001')
            ->set('flow_rate', 2000)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['soil_moisture_before']);
    }

    // ── autorización: viticultor ajeno no puede crear en parcela ajena ─────────

    public function test_policy_denies_create_for_other_viticulturist_plot(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($other);
        $this->makeCampaign($other);

        // El plot pertenece a $viticulturist, $other no debería poder usarlo
        $this->assertFalse($other->can('create-activity', $plot));
    }
}
