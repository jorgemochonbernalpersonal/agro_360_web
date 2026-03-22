<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Pruning;

use App\Livewire\Viticulturist\DigitalNotebook\EditPruning;
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

    private function makeActivityWithPruning($viticulturist, Plot $plot, Campaign $campaign): AgriculturalActivity
    {
        $activity = AgriculturalActivity::create([
            'viticulturist_id'   => $viticulturist->id,
            'plot_id'            => $plot->id,
            'campaign_id'        => $campaign->id,
            'activity_type'      => 'pruning',
            'activity_date'      => now()->format('Y-m-d'),
            'phenological_stage' => 'Reposo invernal',
            'crew_member_id'     => null,
            'is_locked'          => false,
        ]);

        CulturalWork::create([
            'activity_id'                 => $activity->id,
            'work_type'                   => 'poda',
            'pruning_type'                => 'guyot',
            'productive_buds_per_hectare' => 40000,
            'residue_management'          => 'triturado_superficie',
            'hours_worked'                => 6.0,
            'workers_count'               => 2,
            'description'                 => 'Poda inicial en Guyot simple antes de editar.',
        ]);

        return $activity->load('culturalWork');
    }

    // ── mount: campos precargados ──────────────────────────────────────────────

    public function test_mount_fills_all_fields_from_existing_pruning(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithPruning($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPruning::class, ['activity' => $activity])
            ->assertSet('plot_id', $plot->id)
            ->assertSet('campaign_id', $campaign->id)
            ->assertSet('phenological_stage', 'Reposo invernal')
            ->assertSet('pruning_type', 'guyot')
            ->assertSet('productive_buds_per_hectare', 40000)
            ->assertSet('residue_management', 'triturado_superficie')
            ->assertSet('description', 'Poda inicial en Guyot simple antes de editar.');
    }

    // ── update correcto ────────────────────────────────────────────────────────

    public function test_can_update_pruning(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithPruning($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPruning::class, ['activity' => $activity])
            ->set('pruning_type', 'doble_guyot')
            ->set('productive_buds_per_hectare', 55000)
            ->set('description', 'Poda actualizada a Doble Guyot con mayor densidad de yemas.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.pruning.index'));

        $this->assertDatabaseHas('cultural_works', [
            'activity_id'                 => $activity->id,
            'pruning_type'                => 'doble_guyot',
            'productive_buds_per_hectare' => 55000,
        ]);
    }

    // ── update campo BCAM 6 ────────────────────────────────────────────────────

    public function test_can_update_residue_management(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithPruning($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPruning::class, ['activity' => $activity])
            ->set('residue_management', 'retirado')
            ->set('description', 'Poda Guyot con restos de poda retirados de la parcela.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $culturalWork = CulturalWork::where('activity_id', $activity->id)->first();
        $this->assertEquals('retirado', $culturalWork->residue_management);
    }

    // ── validaciones en edición ────────────────────────────────────────────────

    public function test_pruning_type_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithPruning($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPruning::class, ['activity' => $activity])
            ->set('pruning_type', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['pruning_type']);
    }

    public function test_description_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithPruning($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPruning::class, ['activity' => $activity])
            ->set('description', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['description']);
    }

    public function test_invalid_residue_management_fails_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithPruning($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditPruning::class, ['activity' => $activity])
            ->set('residue_management', 'valor_invalido')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['residue_management']);
    }

    // ── bloqueo ────────────────────────────────────────────────────────────────

    public function test_policy_denies_update_for_locked_activity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithPruning($viticulturist, $plot, $campaign);

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
        $other         = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithPruning($viticulturist, $plot, $campaign);

        $this->assertFalse(
            $other->can('update', $activity)
        );
    }
}
