<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\CulturalWork;

use App\Livewire\Viticulturist\DigitalNotebook\CreateCulturalWork;
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
    // ── mount ──────────────────────────────────────────────────────────────────

    public function test_component_renders(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateCulturalWork::class)
            ->assertOk();
    }

    public function test_mount_sets_today_as_activity_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateCulturalWork::class)
            ->assertSet('activity_date', now()->format('Y-m-d'));
    }

    // ── validaciones ──────────────────────────────────────────────────────────

    public function test_validates_work_type_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateCulturalWork::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('work_type', '')
            ->set('description', 'Descripción de la labor cultural.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['work_type']);
    }

    public function test_validates_description_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateCulturalWork::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('work_type', 'laboreo')
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

        Livewire::test(CreateCulturalWork::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('work_type', 'laboreo')
            ->set('description', 'Corto')
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

        Livewire::test(CreateCulturalWork::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', '')
            ->set('work_type', 'laboreo')
            ->set('description', 'Labor de suelo en toda la parcela.')
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

        Livewire::test(CreateCulturalWork::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Brotación')
            ->set('work_type', 'laboreo')
            ->set('description', 'Labor de suelo en toda la parcela.')
            ->set('workType', '')
            ->call('save')
            ->assertHasErrors(['workType']);
    }

    // ── guardado básico ────────────────────────────────────────────────────────

    public function test_can_save_cultural_work(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateCulturalWork::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Desarrollo vegetativo')
            ->set('work_type', 'deshojado')
            ->set('hours_worked', 4.5)
            ->set('workers_count', 3)
            ->set('description', 'Deshojado manual en la zona de racimos.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.cultural.index'));

        $this->assertDatabaseHas('agricultural_activities', [
            'viticulturist_id' => $viticulturist->id,
            'activity_type' => 'cultural',
            'plot_id' => $plot->id,
            'phenological_stage' => 'Desarrollo vegetativo',
        ]);

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type' => 'cultural',
        ])->first();

        $this->assertDatabaseHas('cultural_works', [
            'activity_id' => $activity->id,
            'work_type' => 'deshojado',
            'workers_count' => 3,
        ]);
    }

    // ── poda: campos específicos ───────────────────────────────────────────────

    public function test_can_save_pruning_with_specific_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateCulturalWork::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Reposo invernal')
            ->set('work_type', 'poda')
            ->set('pruning_type', 'guyot')
            ->set('productive_buds_per_hectare', 45000)
            ->set('hours_worked', 8.0)
            ->set('description', 'Poda en verde tipo Guyot, 45000 yemas por hectárea.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type' => 'cultural',
        ])->first();

        $this->assertDatabaseHas('cultural_works', [
            'activity_id' => $activity->id,
            'work_type' => 'poda',
            'pruning_type' => 'guyot',
            'productive_buds_per_hectare' => 45000,
        ]);
    }

    public function test_pruning_fields_not_stored_for_non_pruning_work(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateCulturalWork::class)
            ->set('plot_id', $plot->id)
            ->set('phenological_stage', 'Floración')
            ->set('work_type', 'despuntado')
            ->set('pruning_type', 'guyot')          // se ignora
            ->set('productive_buds_per_hectare', 50000) // se ignora
            ->set('description', 'Despuntado de pámpanos en floración.')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where([
            'viticulturist_id' => $viticulturist->id,
            'activity_type' => 'cultural',
        ])->first();

        $culturalWork = CulturalWork::where('activity_id', $activity->id)->first();
        $this->assertNull($culturalWork->pruning_type);
        $this->assertNull($culturalWork->productive_buds_per_hectare);
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
