<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\PostHarvest;

use App\Livewire\Viticulturist\DigitalNotebook\CreatePostHarvest;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PostHarvestTreatment;
use App\Models\Province;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    // ── mount ──────────────────────────────────────────────────────────────────

    public function test_component_renders(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->assertOk();
    }

    public function test_mount_sets_today_as_activity_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->assertSet('activity_date', now()->format('Y-m-d'));
    }

    public function test_mount_sets_caida_de_hoja_as_default_phenological_stage(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->assertSet('phenological_stage', 'Caída de hoja');
    }

    // ── validaciones ──────────────────────────────────────────────────────────

    public function test_validates_application_type_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Caída de hoja')
            ->set('application_type', '')
            ->set('treated_area_ha', 1.5)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['application_type']);
    }

    public function test_validates_invalid_application_type(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Caída de hoja')
            ->set('application_type', 'tipo_invalido')
            ->set('treated_area_ha', 1.5)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['application_type']);
    }

    public function test_validates_treated_area_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Caída de hoja')
            ->set('application_type', 'copper_treatment')
            ->set('treated_area_ha', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['treated_area_ha']);
    }

    public function test_validates_treated_area_min_0001(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Caída de hoja')
            ->set('application_type', 'copper_treatment')
            ->set('treated_area_ha', 0)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['treated_area_ha']);
    }

    public function test_validates_worktype_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Caída de hoja')
            ->set('application_type', 'copper_treatment')
            ->set('treated_area_ha', 1.5)
            ->set('workType', '')
            ->call('save')
            ->assertHasErrors(['workType']);
    }

    public function test_validates_reentry_interval_max_168(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Caída de hoja')
            ->set('application_type', 'copper_treatment')
            ->set('treated_area_ha', 1.5)
            ->set('reentry_interval_hours', 200)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['reentry_interval_hours']);
    }

    public function test_validates_phenological_stage_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', '')
            ->set('application_type', 'copper_treatment')
            ->set('treated_area_ha', 1.5)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['phenological_stage']);
    }

    // ── guardado básico ────────────────────────────────────────────────────────

    public function test_can_save_post_harvest_treatment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Caída de hoja')
            ->set('application_type', 'copper_treatment')
            ->set('treated_area_ha', 2.5)
            ->set('dose_per_hectare', 3.0)
            ->set('dose_unit', 'kg/ha')
            ->set('water_volume_liters', 300)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.post-harvest.index'));

        $this->assertDatabaseHas('agricultural_activities', [
            'viticulturist_id' => $viticulturist->id,
            'activity_type' => 'post_harvest',
            'plot_id' => $plot->id,
        ]);

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type' => 'post_harvest',
        ])->first();

        $this->assertDatabaseHas('post_harvest_treatments', [
            'activity_id' => $activity->id,
            'application_type' => 'copper_treatment',
        ]);
    }

    // ── campo PAC: reentry_interval_hours ─────────────────────────────────────

    public function test_can_save_with_reentry_interval(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Caída de hoja')
            ->set('application_type', 'copper_treatment')
            ->set('treated_area_ha', 2.5)
            ->set('reentry_interval_hours', 24)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where('viticulturist_id', $viticulturist->id)->first();
        $treatment = PostHarvestTreatment::where('activity_id', $activity->id)->first();

        $this->assertEquals(24, $treatment->reentry_interval_hours);
    }

    public function test_reentry_interval_zero_is_valid(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Caída de hoja')
            ->set('application_type', 'wound_sealing')
            ->set('treated_area_ha', 1.0)
            ->set('reentry_interval_hours', 0)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where('viticulturist_id', $viticulturist->id)->first();
        $treatment = PostHarvestTreatment::where('activity_id', $activity->id)->first();

        $this->assertEquals(0, $treatment->reentry_interval_hours);
    }

    public function test_reentry_interval_is_optional(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePostHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Caída de hoja')
            ->set('application_type', 'foliar_application')
            ->set('treated_area_ha', 1.5)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where('viticulturist_id', $viticulturist->id)->first();
        $treatment = PostHarvestTreatment::where('activity_id', $activity->id)->first();

        $this->assertNull($treatment->reentry_interval_hours);
    }

    // ── autorización ──────────────────────────────────────────────────────────

    public function test_policy_denies_create_for_other_viticulturist_plot(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $plot = $this->makePlot($viticulturist);

        $this->assertFalse($other->can('create-activity', $plot));
    }
    // ── Helpers ────────────────────────────────────────────────────────────────

    private function makePlot($viticulturist): Plot
    {
        $ac = AutonomousCommunity::firstOrCreate(['code' => 'TST'], ['name' => 'Test AC']);
        $prov = Province::firstOrCreate(['code' => '00'], ['name' => 'Test Province', 'autonomous_community_id' => $ac->id]);
        $muni = Municipality::firstOrCreate(['code' => '00000'], ['name' => 'Test Municipality', 'province_id' => $prov->id]);

        return Plot::factory()->create([
            'viticulturist_id' => $viticulturist->id,
            'autonomous_community_id' => $ac->id,
            'province_id' => $prov->id,
            'municipality_id' => $muni->id,
            'active' => true,
        ]);
    }

    private function makeCampaign($viticulturist): Campaign
    {
        return Campaign::factory()->active()->create([
            'viticulturist_id' => $viticulturist->id,
            'year' => now()->year,
        ]);
    }
}
