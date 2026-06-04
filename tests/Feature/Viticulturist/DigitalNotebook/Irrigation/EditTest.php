<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Irrigation;

use App\Livewire\Viticulturist\DigitalNotebook\EditIrrigation;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Irrigation;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    // ── mount: campos precargados ──────────────────────────────────────────────

    public function test_mount_fills_all_fields_from_existing_irrigation(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithIrrigation($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditIrrigation::class, ['activity' => $activity])
            ->assertSet('plot_id', $plot->id)
            ->assertSet('campaign_id', $campaign->id)
            ->assertSet('phenological_stage', 'Envero')
            ->assertSet('irrigation_method', 'goteo')
            ->assertSet('duration_minutes', 60)
            ->assertSet('water_source', 'Pozo legalizado')
            ->assertSet('water_concession', 'EXP-ORIG-2024');
    }

    // ── update correcto ────────────────────────────────────────────────────────

    public function test_can_update_irrigation(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithIrrigation($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditIrrigation::class, ['activity' => $activity])
            ->set('water_volume', 900)
            ->set('duration_minutes', 120)
            ->set('water_concession', 'EXP-UPDATED-2024')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.irrigation.index'));

        $this->assertDatabaseHas('irrigations', [
            'activity_id' => $activity->id,
            'duration_minutes' => 120,
            'water_concession' => 'EXP-UPDATED-2024',
        ]);
    }

    // ── PAC: campos obligatorios en edición ───────────────────────────────────

    public function test_pac_fields_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithIrrigation($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditIrrigation::class, ['activity' => $activity])
            ->set('water_source', '')
            ->set('water_concession', '')
            ->set('flow_rate', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['water_source', 'water_concession', 'flow_rate']);
    }

    // ── actualización campos PAC ───────────────────────────────────────────────

    public function test_can_update_pac_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithIrrigation($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditIrrigation::class, ['activity' => $activity])
            ->set('water_source', 'Comunidad de regantes')
            ->set('water_concession', 'CR-2024/NEW')
            ->set('flow_rate', 3500.0)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('irrigations', [
            'activity_id' => $activity->id,
            'water_source' => 'Comunidad de regantes',
            'water_concession' => 'CR-2024/NEW',
        ]);
    }

    // ── actualización humedad de suelo ────────────────────────────────────────

    public function test_can_update_soil_moisture_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithIrrigation($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditIrrigation::class, ['activity' => $activity])
            ->set('soil_moisture_before', 18.5)
            ->set('soil_moisture_after', 52.0)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $irrigation = Irrigation::where('activity_id', $activity->id)->first();
        $this->assertEquals('18.50', $irrigation->soil_moisture_before);
        $this->assertEquals('52.00', $irrigation->soil_moisture_after);
    }

    // ── bloqueo: policy deniega update para actividad bloqueada ──────────────

    public function test_policy_denies_update_for_locked_activity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithIrrigation($viticulturist, $plot, $campaign);

        $activity->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $viticulturist->id,
        ]);

        $this->assertFalse(
            $viticulturist->can('update', $activity->fresh())
        );
    }

    // ── autorización: policy deniega update a otro viticulturist ──────────────

    public function test_policy_denies_update_for_other_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithIrrigation($viticulturist, $plot, $campaign);

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

    private function makeActivityWithIrrigation($viticulturist, Plot $plot, Campaign $campaign): AgriculturalActivity
    {
        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $viticulturist->id,
            'plot_id' => $plot->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'irrigation',
            'activity_date' => now()->format('Y-m-d'),
            'phenological_stage' => 'Envero',
            'crew_member_id' => null,
            'is_locked' => false,
        ]);

        Irrigation::create([
            'activity_id' => $activity->id,
            'water_volume' => 600.0,
            'irrigation_method' => 'goteo',
            'duration_minutes' => 60,
            'soil_moisture_before' => 20.0,
            'soil_moisture_after' => 45.0,
            'water_source' => 'Pozo legalizado',
            'water_concession' => 'EXP-ORIG-2024',
            'flow_rate' => 2000.0,
        ]);

        return $activity->load('irrigation');
    }
}
