<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Observation;

use App\Livewire\Viticulturist\DigitalNotebook\CreateObservation;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Municipality;
use App\Models\Observation;
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

        Livewire::test(CreateObservation::class)
            ->assertOk();
    }

    public function test_mount_sets_today_as_activity_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateObservation::class)
            ->assertSet('activity_date', now()->format('Y-m-d'));
    }

    // ── validaciones básicas ──────────────────────────────────────────────────

    public function test_validates_observation_type_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateObservation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('observation_type', '')
            ->set('description', 'Presencia de oídio en hojas basales')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['observation_type']);
    }

    public function test_validates_description_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateObservation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('observation_type', 'plaga')
            ->set('description', '')
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

        Livewire::test(CreateObservation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', '')
            ->set('observation_type', 'plaga')
            ->set('description', 'Presencia de trips en flores')
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

        Livewire::test(CreateObservation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('observation_type', 'plaga')
            ->set('description', 'Presencia de trips en flores')
            ->set('workType', '')
            ->call('save')
            ->assertHasErrors(['workType']);
    }

    public function test_validates_severity_must_be_valid(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateObservation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('observation_type', 'plaga')
            ->set('description', 'Presencia de trips')
            ->set('severity', 'extrema') // Valor no válido
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['severity']);
    }

    // ── guardado completo ──────────────────────────────────────────────────────

    public function test_can_save_observation(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateObservation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Floración')
            ->set('observation_type', 'plaga')
            ->set('description', 'Presencia de trips en flores. Nivel bajo, monitoreo preventivo.')
            ->set('severity', 'leve')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.observation.index'));

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type'    => 'observation',
        ])->first();

        $this->assertDatabaseHas('observations', [
            'activity_id'      => $activity->id,
            'observation_type' => 'plaga',
            'severity'         => 'leve',
        ]);
    }

    public function test_can_save_observation_with_action_taken(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateObservation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Envero')
            ->set('observation_type', 'enfermedad')
            ->set('description', 'Síntomas de mildiu en pámpanos')
            ->set('severity', 'moderada')
            ->set('action_taken', 'Aplicado fungicida cúprico')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type'    => 'observation',
        ])->first();

        $this->assertDatabaseHas('observations', [
            'activity_id'      => $activity->id,
            'observation_type' => 'enfermedad',
            'action_taken'     => 'Aplicado fungicida cúprico',
        ]);
    }

    public function test_can_save_general_observation_without_severity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateObservation::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Maduración')
            ->set('observation_type', 'general')
            ->set('description', 'Estado general de la parcela bueno. Maduración homogénea.')
            ->set('severity', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $observation = Observation::whereHas('activity', fn ($q) => $q->where('viticulturist_id', $viticulturist->id))->first();
        $this->assertEmpty($observation->severity);
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
