<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Observation;

use App\Livewire\Viticulturist\DigitalNotebook\EditObservation;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Municipality;
use App\Models\Observation;
use App\Models\Plot;
use App\Models\Province;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    // ── mount: campos precargados ──────────────────────────────────────────────

    public function test_mount_fills_all_fields_from_existing_observation(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithObservation($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditObservation::class, ['activity' => $activity])
            ->assertSet('plot_id', $plot->id)
            ->assertSet('campaign_id', $campaign->id)
            ->assertSet('phenological_stage', 'Floración')
            ->assertSet('observation_type', 'plaga')
            ->assertSet('description', 'Presencia de trips en flores')
            ->assertSet('severity', 'leve')
            ->assertSet('threshold_exceeded', false);
    }

    // ── update correcto ────────────────────────────────────────────────────────

    public function test_can_update_observation(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithObservation($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditObservation::class, ['activity' => $activity])
            ->set('description', 'Actualizado: mildiu incipiente en hojas')
            ->set('severity', 'moderada')
            ->set('observation_type', 'enfermedad')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.observation.index'));

        $this->assertDatabaseHas('observations', [
            'activity_id' => $activity->id,
            'observation_type' => 'enfermedad',
            'severity' => 'moderada',
        ]);
    }

    // ── update con campos IPM ─────────────────────────────────────────────────

    public function test_can_update_ipm_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithObservation($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        $followUpDate = now()->addDays(14)->format('Y-m-d');

        Livewire::test(EditObservation::class, ['activity' => $activity])
            ->set('affected_area_percentage', 45.75)
            ->set('threshold_exceeded', true)
            ->set('follow_up_date', $followUpDate)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $observation = Observation::where('activity_id', $activity->id)->first();
        $this->assertEquals('45.75', $observation->affected_area_percentage);
        $this->assertTrue($observation->threshold_exceeded);
        $this->assertEquals($followUpDate, $observation->follow_up_date->format('Y-m-d'));
    }

    // ── validaciones en edición ────────────────────────────────────────────────

    public function test_observation_type_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithObservation($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditObservation::class, ['activity' => $activity])
            ->set('observation_type', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['observation_type']);
    }

    public function test_description_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithObservation($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditObservation::class, ['activity' => $activity])
            ->set('description', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['description']);
    }

    public function test_invalid_severity_fails_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithObservation($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditObservation::class, ['activity' => $activity])
            ->set('severity', 'critica') // Valor no válido
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['severity']);
    }

    // ── bloqueo ────────────────────────────────────────────────────────────────

    public function test_policy_denies_update_for_locked_activity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $activity = $this->makeActivityWithObservation($viticulturist, $plot, $campaign);

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
        $activity = $this->makeActivityWithObservation($viticulturist, $plot, $campaign);

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

    private function makeActivityWithObservation($viticulturist, Plot $plot, Campaign $campaign): AgriculturalActivity
    {
        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $viticulturist->id,
            'plot_id' => $plot->id,
            'campaign_id' => $campaign->id,
            'activity_type' => 'observation',
            'activity_date' => now()->format('Y-m-d'),
            'phenological_stage' => 'Floración',
            'crew_member_id' => null,
            'is_locked' => false,
        ]);

        Observation::create([
            'activity_id' => $activity->id,
            'observation_type' => 'plaga',
            'description' => 'Presencia de trips en flores',
            'severity' => 'leve',
            'affected_area_percentage' => 20.0,
            'threshold_exceeded' => false,
            'follow_up_date' => now()->addDays(7)->format('Y-m-d'),
            'action_taken' => null,
        ]);

        return $activity->load('observation');
    }
}
