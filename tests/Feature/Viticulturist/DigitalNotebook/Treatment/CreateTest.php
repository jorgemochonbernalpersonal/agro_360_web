<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Treatment;

use App\Livewire\Viticulturist\DigitalNotebook\CreatePhytosanitaryTreatment;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\FieldApplicator;
use App\Models\Municipality;
use App\Models\PhytosanitaryProduct;
use App\Models\Plot;
use App\Models\Province;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    // ── Render ─────────────────────────────────────────────────────────────────

    public function test_component_renders_for_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $this->actingAs($viticulturist);

        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->assertStatus(200);
    }

    // ── Validación campos requeridos ───────────────────────────────────────────

    public function test_validates_required_pac_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $this->actingAs($viticulturist);

        // activity_date y campaign_id se rellenan en mount() — no son errores esperados
        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->call('save')
            ->assertHasErrors([
                'plot_id',
                'phenological_stage',
                'product_id',
                'dose_per_hectare',
                'area_treated',
                'treatment_justification',
                'reentry_period_days',
                'spray_volume',
            ]);
    }

    public function test_treatment_justification_minimum_length(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $this->actingAs($viticulturist);

        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('treatment_justification', 'Corto')
            ->call('save')
            ->assertHasErrors(['treatment_justification']);
    }

    // ── ROPO: required_without field_applicator_id ─────────────────────────────

    public function test_ropo_required_when_no_applicator_selected(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $this->actingAs($viticulturist);

        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('field_applicator_id', '')
            ->set('applicator_ropo_number', '')
            ->call('save')
            ->assertHasErrors(['applicator_ropo_number']);
    }

    public function test_ropo_always_required_even_when_applicator_selected(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $product = $this->makeProduct($viticulturist);
        $applicator = FieldApplicator::create([
            'viticulturist_id' => $viticulturist->id,
            'name' => 'Aplicador Test',
            'ropo_number' => 'ROPO-123',
            'ropo_category' => 'basic',
            'active' => true,
        ]);

        $this->actingAs($viticulturist);

        // ROPO es siempre obligatorio — borrar el campo auto-rellenado sigue siendo error
        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('plot_id', $plot->id)
            ->set('campaign_id', $campaign->id)
            ->set('activity_date', now()->format('Y-m-d'))
            ->set('phenological_stage', 'Floración')
            ->set('product_id', $product->id)
            ->set('dose_per_hectare', 2.5)
            ->set('area_treated', 1.0)
            ->set('treatment_justification', 'Detección de mildiu en hojas basales.')
            ->set('reentry_period_days', 3)
            ->set('spray_volume', 400.0)
            ->set('field_applicator_id', $applicator->id)
            ->set('applicator_ropo_number', '') // usuario borra el ROPO auto-rellenado
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['applicator_ropo_number']);
    }

    // ── Auto-fill ROPO desde aplicador ────────────────────────────────────────

    public function test_selecting_applicator_autofills_ropo_number(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $applicator = FieldApplicator::create([
            'viticulturist_id' => $viticulturist->id,
            'name' => 'Aplicador Auto',
            'ropo_number' => 'ROPO-AUTO-999',
            'ropo_category' => 'qualified',
            'active' => true,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('field_applicator_id', $applicator->id)
            ->assertSet('applicator_ropo_number', 'ROPO-AUTO-999');
    }

    // ── Asesoramiento: fecha requerida cuando under_advisory = true ────────────

    public function test_advisory_date_required_when_under_advisory(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $this->actingAs($viticulturist);

        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('under_advisory', true)
            ->set('advisory_recommendation_date', '')
            ->call('save')
            ->assertHasErrors(['advisory_recommendation_date']);
    }

    public function test_advisory_date_not_required_when_not_under_advisory(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $this->actingAs($viticulturist);

        // Solo validamos que advisory_recommendation_date no da error cuando under_advisory=false
        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('under_advisory', false)
            ->set('advisory_recommendation_date', '')
            ->call('save')
            ->assertHasNoErrors(['advisory_recommendation_date']);
    }

    // ── workType obligatorio ───────────────────────────────────────────────────

    public function test_worktype_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $product = $this->makeProduct($viticulturist);
        $this->actingAs($viticulturist);

        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('plot_id', $plot->id)
            ->set('campaign_id', $campaign->id)
            ->set('activity_date', now()->format('Y-m-d'))
            ->set('phenological_stage', 'Floración')
            ->set('product_id', $product->id)
            ->set('dose_per_hectare', 2.5)
            ->set('area_treated', 1.0)
            ->set('treatment_justification', 'Detección de mildiu en hojas basales.')
            ->set('applicator_ropo_number', 'ROPO-001')
            ->set('reentry_period_days', 3)
            ->set('spray_volume', 400.0)
            ->set('workType', '')
            ->call('save')
            ->assertHasErrors(['workType']);
    }

    // ── Guardado completo ──────────────────────────────────────────────────────

    public function test_saves_treatment_with_all_pac_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $product = $this->makeProduct($viticulturist);
        $this->actingAs($viticulturist);

        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('plot_id', $plot->id)
            ->set('campaign_id', $campaign->id)
            ->set('activity_date', now()->format('Y-m-d'))
            ->set('phenological_stage', 'Floración')
            ->set('product_id', $product->id)
            ->set('dose_per_hectare', 2.5)
            ->set('area_treated', 1.0)
            ->set('treatment_justification', 'Detección de mildiu en hojas basales.')
            ->set('applicator_ropo_number', 'ROPO-PAC-001')
            ->set('reentry_period_days', 3)
            ->set('spray_volume', 400.0)
            ->set('water_volume_liters_ha', 200.0)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.treatment.index'));

        $activity = AgriculturalActivity::where('viticulturist_id', $viticulturist->id)
            ->where('activity_type', 'phytosanitary')
            ->firstOrFail();

        $this->assertDatabaseHas('phytosanitary_treatments', [
            'activity_id' => $activity->id,
            'product_id' => $product->id,
            'treatment_justification' => 'Detección de mildiu en hojas basales.',
            'applicator_ropo_number' => 'ROPO-PAC-001',
            'reentry_period_days' => 3,
        ]);
    }

    public function test_saves_treatment_with_buffer_zone(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $product = $this->makeProduct($viticulturist);
        $this->actingAs($viticulturist);

        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('plot_id', $plot->id)
            ->set('campaign_id', $campaign->id)
            ->set('activity_date', now()->format('Y-m-d'))
            ->set('phenological_stage', 'Floración')
            ->set('product_id', $product->id)
            ->set('dose_per_hectare', 2.5)
            ->set('area_treated', 1.0)
            ->set('treatment_justification', 'Detección de mildiu en hojas basales.')
            ->set('applicator_ropo_number', 'ROPO-001')
            ->set('reentry_period_days', 3)
            ->set('spray_volume', 400.0)
            ->set('buffer_zone_respected', true)
            ->set('distance_to_water_m', 5.0)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where('viticulturist_id', $viticulturist->id)
            ->where('activity_type', 'phytosanitary')
            ->firstOrFail();

        $this->assertDatabaseHas('phytosanitary_treatments', [
            'activity_id' => $activity->id,
            'buffer_zone_respected' => true,
            'distance_to_water_m' => 5.0,
        ]);
    }

    public function test_saves_treatment_with_advisory_and_ipm_flags(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $product = $this->makeProduct($viticulturist);
        $this->actingAs($viticulturist);

        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('plot_id', $plot->id)
            ->set('campaign_id', $campaign->id)
            ->set('activity_date', now()->format('Y-m-d'))
            ->set('phenological_stage', 'Floración')
            ->set('product_id', $product->id)
            ->set('dose_per_hectare', 2.5)
            ->set('area_treated', 1.0)
            ->set('treatment_justification', 'Detección de mildiu en hojas basales.')
            ->set('applicator_ropo_number', 'ROPO-001')
            ->set('reentry_period_days', 3)
            ->set('spray_volume', 400.0)
            ->set('under_advisory', true)
            ->set('advisory_recommendation_date', now()->subDay()->format('Y-m-d'))
            ->set('plague_monitoring', true)
            ->set('prior_non_chemical_methods', true)
            ->set('biological_control', true)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $activity = AgriculturalActivity::where('viticulturist_id', $viticulturist->id)
            ->where('activity_type', 'phytosanitary')
            ->firstOrFail();

        $this->assertDatabaseHas('phytosanitary_treatments', [
            'activity_id' => $activity->id,
            'under_advisory' => true,
            'plague_monitoring' => true,
            'prior_non_chemical_methods' => true,
            'biological_control' => true,
        ]);
    }

    // ── Cálculo automático dosis total ────────────────────────────────────────

    public function test_total_dose_calculated_from_dose_and_area(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);
        $this->actingAs($viticulturist);

        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('dose_per_hectare', 2.5)
            ->set('area_treated', 3.0)
            ->assertSet('total_dose', 7.5);
    }

    // ── applicationsThisCampaign computed property ────────────────────────────

    public function test_applications_this_campaign_counts_existing_treatments(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign = $this->makeCampaign($viticulturist);
        $plot = $this->makePlot($viticulturist);
        $product = $this->makeProduct($viticulturist);

        // Crear una actividad previa con el mismo producto en esta campaña
        AgriculturalActivity::factory()
            ->withPhytosanitaryTreatment(['product_id' => $product->id])
            ->create([
                'viticulturist_id' => $viticulturist->id,
                'plot_id' => $plot->id,
                'campaign_id' => $campaign->id,
                'activity_type' => 'phytosanitary',
            ]);

        $this->actingAs($viticulturist);

        Livewire::test(CreatePhytosanitaryTreatment::class)
            ->set('campaign_id', $campaign->id)
            ->set('product_id', $product->id)
            ->assertSet('applicationsThisCampaign', 1);
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

    private function makeProduct($viticulturist): PhytosanitaryProduct
    {
        return PhytosanitaryProduct::create([
            'user_id' => $viticulturist->id,
            'name' => 'Fungicida Test',
            'active_ingredient' => 'Cobre',
            'registration_number' => 'ES-00000001',
            'withdrawal_period_days' => 14,
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

    private function validPayload(Plot $plot, PhytosanitaryProduct $product, Campaign $campaign): array
    {
        return [
            'plot_id' => $plot->id,
            'campaign_id' => $campaign->id,
            'activity_date' => now()->format('Y-m-d'),
            'phenological_stage' => 'Floración',
            'product_id' => $product->id,
            'dose_per_hectare' => 2.5,
            'area_treated' => 1.0,
            'workType' => 'individual',
            'crew_member_id' => '', // se omite individual sin crew
            'treatment_justification' => 'Detección de mildiu en hojas basales.',
            'applicator_ropo_number' => 'ROPO-TEST-001',
            'reentry_period_days' => 3,
            'spray_volume' => 400.0,
        ];
    }
}
