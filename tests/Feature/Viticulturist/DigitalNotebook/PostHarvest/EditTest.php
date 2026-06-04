<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\PostHarvest;

use App\Livewire\Viticulturist\DigitalNotebook\EditPostHarvest;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PostHarvestTreatment;
use App\Models\Province;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    // ── mount: campos precargados ──────────────────────────────────────────────

    public function test_mount_fills_all_fields_from_existing_treatment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPostHarvest::class, ['activity' => $activity])
            ->assertSet('plot_id', $plot->id)
            ->assertSet('campaign_id', $campaign->id)
            ->assertSet('phenological_stage', 'Caída de hoja')
            ->assertSet('application_type', 'copper_treatment')
            ->assertSet('reentry_interval_hours', 24);
    }

    // ── update correcto ────────────────────────────────────────────────────────

    public function test_can_update_treatment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPostHarvest::class, ['activity' => $activity])
            ->set('application_type', 'sulfur_treatment')
            ->set('treated_area_ha', 3.0)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.post-harvest.index'));

        $this->assertDatabaseHas('post_harvest_treatments', [
            'activity_id' => $activity->id,
            'application_type' => 'sulfur_treatment',
        ]);
    }

    // ── update campo PAC ──────────────────────────────────────────────────────

    public function test_can_update_reentry_interval(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPostHarvest::class, ['activity' => $activity])
            ->set('reentry_interval_hours', 48)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $treatment = PostHarvestTreatment::where('activity_id', $activity->id)->first();
        $this->assertEquals(48, $treatment->reentry_interval_hours);
    }

    public function test_can_clear_reentry_interval_to_null(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPostHarvest::class, ['activity' => $activity])
            ->set('reentry_interval_hours', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $treatment = PostHarvestTreatment::where('activity_id', $activity->id)->first();
        $this->assertNull($treatment->reentry_interval_hours);
    }

    // ── validaciones en edición ────────────────────────────────────────────────

    public function test_application_type_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPostHarvest::class, ['activity' => $activity])
            ->set('application_type', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['application_type']);
    }

    public function test_treated_area_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPostHarvest::class, ['activity' => $activity])
            ->set('treated_area_ha', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['treated_area_ha']);
    }

    public function test_reentry_interval_max_168_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPostHarvest::class, ['activity' => $activity])
            ->set('reentry_interval_hours', 200)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['reentry_interval_hours']);
    }

    // ── bloqueo ────────────────────────────────────────────────────────────────

    public function test_policy_denies_update_for_locked_activity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign);

        $activity->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $viticulturist->id,
        ]);

        $this->assertFalse(
            $viticulturist->can('update', $activity->fresh())
        );
    }

    // ── autorización ──────────────────────────────────────────────────────────

    public function test_policy_denies_update_for_other_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign);

        $this->assertFalse(
            $other->can('update', $activity)
        );
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

    private function makeActivityWithTreatment($viticulturist, Plot $plot, Campaign $campaign): AgriculturalActivity
    {
        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $viticulturist->id,
            'plot_id' => $plot->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'post_harvest',
            'activity_date' => now()->format('Y-m-d'),
            'phenological_stage' => 'Caída de hoja',
            'crew_member_id' => null,
            'is_locked' => false,
        ]);

        PostHarvestTreatment::create([
            'activity_id' => $activity->id,
            'application_type' => 'copper_treatment',
            'treated_area_ha' => 2.5,
            'dose_per_hectare' => 3.0,
            'dose_unit' => 'kg/ha',
            'water_volume_liters' => 300.0,
            'reentry_interval_hours' => 24,
            'notes' => null,
        ]);

        return $activity->load('postHarvestTreatment');
    }
}
