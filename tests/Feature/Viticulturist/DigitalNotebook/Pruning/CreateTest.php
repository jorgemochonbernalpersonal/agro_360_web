<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Pruning;

use App\Livewire\Viticulturist\DigitalNotebook\CreatePruning;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\CulturalWork;
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

        Livewire::test(CreatePruning::class)
            ->assertOk();
    }

    public function test_mount_sets_today_as_activity_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePruning::class)
            ->assertSet('activity_date', now()->format('Y-m-d'));
    }

    public function test_mount_sets_reposo_invernal_as_phenological_stage(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePruning::class)
            ->assertSet('phenological_stage', 'Reposo invernal');
    }

    // ── validaciones ──────────────────────────────────────────────────────────

    public function test_validates_pruning_type_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePruning::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Reposo invernal')
            ->set('pruning_type', '')
            ->set('description', 'Poda en Guyot simple en toda la parcela.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['pruning_type']);
    }

    public function test_validates_description_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePruning::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Reposo invernal')
            ->set('pruning_type', 'guyot')
            ->set('description', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['description']);
    }

    public function test_validates_description_min_10_chars(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePruning::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Reposo invernal')
            ->set('pruning_type', 'guyot')
            ->set('description', 'Corta')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['description']);
    }

    public function test_validates_phenological_stage_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePruning::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', '')
            ->set('pruning_type', 'guyot')
            ->set('description', 'Poda en Guyot simple en toda la parcela.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['phenological_stage']);
    }

    public function test_validates_worktype_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePruning::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Reposo invernal')
            ->set('pruning_type', 'guyot')
            ->set('description', 'Poda en Guyot simple en toda la parcela.')
            ->set('workType', '')
            ->call('save')
            ->assertHasErrors(['workType']);
    }

    public function test_validates_invalid_residue_management_fails(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePruning::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Reposo invernal')
            ->set('pruning_type', 'guyot')
            ->set('description', 'Poda en Guyot simple en toda la parcela.')
            ->set('residue_management', 'valor_invalido')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['residue_management']);
    }

    // ── guardado básico ────────────────────────────────────────────────────────

    public function test_can_save_pruning(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePruning::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Reposo invernal')
            ->set('pruning_type', 'guyot')
            ->set('productive_buds_per_hectare', 40000)
            ->set('hours_worked', 8.0)
            ->set('workers_count', 2)
            ->set('description', 'Poda en Guyot simple en toda la parcela.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.pruning.index'));

        $this->assertDatabaseHas('agricultural_activities', [
            'viticulturist_id'   => $viticulturist->id,
            'activity_type'      => 'pruning',
            'plot_id'            => $plot->id,
            'phenological_stage' => 'Reposo invernal',
        ]);

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type'    => 'pruning',
        ])->first();

        $this->assertDatabaseHas('cultural_works', [
            'activity_id'                 => $activity->id,
            'work_type'                   => 'poda',
            'pruning_type'                => 'guyot',
            'productive_buds_per_hectare' => 40000,
        ]);
    }

    // ── campo BCAM 6: residue_management ──────────────────────────────────────

    public function test_can_save_pruning_with_residue_management(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePruning::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Reposo invernal')
            ->set('pruning_type', 'doble_guyot')
            ->set('residue_management', 'triturado_incorporado')
            ->set('description', 'Poda Doble Guyot con triturado del ramón e incorporación al suelo.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type'    => 'pruning',
        ])->first();

        $this->assertDatabaseHas('cultural_works', [
            'activity_id'        => $activity->id,
            'pruning_type'       => 'doble_guyot',
            'residue_management' => 'triturado_incorporado',
        ]);
    }

    public function test_residue_management_is_optional(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePruning::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Reposo invernal')
            ->set('pruning_type', 'vaso')
            ->set('description', 'Poda en vaso tradicional sin especificar gestión de ramón.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type'    => 'pruning',
        ])->first();

        $culturalWork = CulturalWork::where('activity_id', $activity->id)->first();
        $this->assertNull($culturalWork->residue_management);
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
