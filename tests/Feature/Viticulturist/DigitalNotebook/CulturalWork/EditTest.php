<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\CulturalWork;

use App\Livewire\Viticulturist\DigitalNotebook\EditCulturalWork;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\CulturalWork;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
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

    private function makeActivityWithWork($viticulturist, Plot $plot, Campaign $campaign, string $workType = 'laboreo'): AgriculturalActivity
    {
        $activity = AgriculturalActivity::create([
            'viticulturist_id'   => $viticulturist->id,
            'plot_id'            => $plot->id,
            'campaign_id'        => $campaign->id,
            'activity_type'      => 'cultural',
            'activity_date'      => now()->format('Y-m-d'),
            'phenological_stage' => 'Envero',
            'crew_member_id'     => null,
            'is_locked'          => false,
        ]);

        CulturalWork::create([
            'activity_id'   => $activity->id,
            'work_type'     => $workType,
            'hours_worked'  => 5.0,
            'workers_count' => 2,
            'description'   => 'Labor de suelo original antes de editar.',
        ]);

        return $activity->load('culturalWork');
    }

    // ── mount: campos precargados ──────────────────────────────────────────────

    public function test_mount_fills_all_fields_from_existing_work(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithWork($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditCulturalWork::class, ['activity' => $activity])
            ->assertSet('plot_id', $plot->id)
            ->assertSet('campaign_id', $campaign->id)
            ->assertSet('phenological_stage', 'Envero')
            ->assertSet('work_type', 'laboreo')
            ->assertSet('workers_count', 2)
            ->assertSet('description', 'Labor de suelo original antes de editar.');
    }

    // ── update correcto ────────────────────────────────────────────────────────

    public function test_can_update_cultural_work(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithWork($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditCulturalWork::class, ['activity' => $activity])
            ->set('description', 'Descripción actualizada de la labor de suelo.')
            ->set('hours_worked', 7.5)
            ->set('workers_count', 4)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.cultural.index'));

        $this->assertDatabaseHas('cultural_works', [
            'activity_id'  => $activity->id,
            'description'  => 'Descripción actualizada de la labor de suelo.',
            'workers_count' => 4,
        ]);
    }

    // ── validaciones en edición ────────────────────────────────────────────────

    public function test_description_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithWork($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditCulturalWork::class, ['activity' => $activity])
            ->set('description', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['description']);
    }

    public function test_description_min_10_chars_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithWork($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditCulturalWork::class, ['activity' => $activity])
            ->set('description', 'Corto')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['description']);
    }

    // ── poda: actualizar campos específicos ────────────────────────────────────

    public function test_can_update_to_pruning_with_specific_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithWork($viticulturist, $plot, $campaign, 'poda');

        $this->actingAs($viticulturist);

        Livewire::test(EditCulturalWork::class, ['activity' => $activity])
            ->set('work_type', 'poda')
            ->set('pruning_type', 'doble_guyot')
            ->set('productive_buds_per_hectare', 55000)
            ->set('description', 'Poda en verde tipo Doble Guyot, 55000 yemas por hectárea.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cultural_works', [
            'activity_id'                 => $activity->id,
            'pruning_type'               => 'doble_guyot',
            'productive_buds_per_hectare' => 55000,
        ]);
    }

    // ── bloqueo: policy deniega update para actividad bloqueada ──────────────

    public function test_policy_denies_update_for_locked_activity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithWork($viticulturist, $plot, $campaign);

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
        $other         = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithWork($viticulturist, $plot, $campaign);

        $this->assertFalse(
            $other->can('update', $activity)
        );
    }
}
