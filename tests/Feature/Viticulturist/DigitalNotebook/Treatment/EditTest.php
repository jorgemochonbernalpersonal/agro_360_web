<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Treatment;

use App\Livewire\Viticulturist\DigitalNotebook\EditPhytosanitaryTreatment;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\FieldApplicator;
use App\Models\Municipality;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
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

    private function makeProduct($viticulturist): PhytosanitaryProduct
    {
        return PhytosanitaryProduct::create([
            'user_id'                => $viticulturist->id,
            'name'                   => 'Fungicida Test',
            'active_ingredient'      => 'Cobre',
            'registration_number'    => 'ES-00000002',
            'withdrawal_period_days' => 14,
            'active'                 => true,
        ]);
    }

    private function makeCampaign($viticulturist): Campaign
    {
        return Campaign::factory()->active()->create([
            'viticulturist_id' => $viticulturist->id,
            'year'             => now()->year,
        ]);
    }

    private function makeActivityWithTreatment($viticulturist, Plot $plot, Campaign $campaign, PhytosanitaryProduct $product): AgriculturalActivity
    {
        $activity = AgriculturalActivity::create([
            'viticulturist_id'   => $viticulturist->id,
            'plot_id'            => $plot->id,
            'campaign_id'        => $campaign->id,
            'activity_type'      => 'phytosanitary',
            'activity_date'      => now()->format('Y-m-d'),
            'phenological_stage' => 'Brotación',
            'crew_member_id'     => null,
            'is_locked'          => false,
        ]);

        PhytosanitaryTreatment::create([
            'activity_id'             => $activity->id,
            'product_id'              => $product->id,
            'dose_per_hectare'        => 2.0,
            'area_treated'            => 1.5,
            'total_dose'              => 3.0,
            'application_method'      => 'pulverización',
            'treatment_justification' => 'Presencia de oídio en pámpanos.',
            'applicator_ropo_number'  => 'ROPO-ORIG-001',
            'reentry_period_days'     => 2,
            'spray_volume'            => 300.0,
            'under_advisory'          => false,
            'prior_non_chemical_methods' => false,
            'plague_monitoring'       => false,
            'manual_mechanical_control' => false,
            'biological_control'      => false,
            'cultural_preventions'    => false,
        ]);

        return $activity->load('phytosanitaryTreatment');
    }

    // ── mount: campos precargados ──────────────────────────────────────────────

    public function test_mount_fills_all_fields_from_existing_treatment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $product       = $this->makeProduct($viticulturist);
        $activity      = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign, $product);

        $this->actingAs($viticulturist);

        Livewire::test(EditPhytosanitaryTreatment::class, ['activity' => $activity])
            ->assertSet('plot_id', $plot->id)
            ->assertSet('campaign_id', $campaign->id)
            ->assertSet('phenological_stage', 'Brotación')
            ->assertSet('product_id', $product->id)
            ->assertSet('dose_per_hectare', '2.000')
            ->assertSet('area_treated', '1.500')
            ->assertSet('treatment_justification', 'Presencia de oídio en pámpanos.')
            ->assertSet('applicator_ropo_number', 'ROPO-ORIG-001')
            ->assertSet('reentry_period_days', 2)
            ->assertSet('under_advisory', false);
    }

    // ── update correcto ────────────────────────────────────────────────────────

    public function test_can_update_treatment(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $product       = $this->makeProduct($viticulturist);
        $activity      = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign, $product);

        $this->actingAs($viticulturist);

        Livewire::test(EditPhytosanitaryTreatment::class, ['activity' => $activity])
            ->set('treatment_justification', 'Actualizado: detección de mildiu severo.')
            ->set('dose_per_hectare', 3.0)
            ->set('area_treated', 2.0)
            ->set('reentry_period_days', 5)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.treatment.index'));

        $this->assertDatabaseHas('phytosanitary_treatments', [
            'activity_id'             => $activity->id,
            'treatment_justification' => 'Actualizado: detección de mildiu severo.',
            'reentry_period_days'     => 5,
        ]);
    }

    // ── auto-fill ROPO al cambiar aplicador ───────────────────────────────────

    public function test_changing_applicator_autofills_ropo(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $product       = $this->makeProduct($viticulturist);
        $activity      = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign, $product);

        $applicator = FieldApplicator::create([
            'viticulturist_id' => $viticulturist->id,
            'name'             => 'Nuevo Aplicador',
            'ropo_number'      => 'ROPO-NEW-777',
            'ropo_category'    => 'qualified',
            'active'           => true,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(EditPhytosanitaryTreatment::class, ['activity' => $activity])
            ->set('field_applicator_id', $applicator->id)
            ->assertSet('applicator_ropo_number', 'ROPO-NEW-777');
    }

    // ── campo buffer_zone ──────────────────────────────────────────────────────

    public function test_can_update_buffer_zone_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $product       = $this->makeProduct($viticulturist);
        $activity      = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign, $product);

        $this->actingAs($viticulturist);

        Livewire::test(EditPhytosanitaryTreatment::class, ['activity' => $activity])
            ->set('buffer_zone_respected', true)
            ->set('distance_to_water_m', 10.0)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('phytosanitary_treatments', [
            'activity_id'           => $activity->id,
            'buffer_zone_respected' => true,
            'distance_to_water_m'   => 10.0,
        ]);
    }

    // ── advisory_recommendation_date ──────────────────────────────────────────

    public function test_advisory_date_required_when_under_advisory(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $product       = $this->makeProduct($viticulturist);
        $activity      = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign, $product);

        $this->actingAs($viticulturist);

        Livewire::test(EditPhytosanitaryTreatment::class, ['activity' => $activity])
            ->set('under_advisory', true)
            ->set('advisory_recommendation_date', '')
            ->call('update')
            ->assertHasErrors(['advisory_recommendation_date']);
    }

    public function test_can_save_advisory_recommendation_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $product       = $this->makeProduct($viticulturist);
        $activity      = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign, $product);
        $date          = now()->subDays(2)->format('Y-m-d');

        $this->actingAs($viticulturist);

        Livewire::test(EditPhytosanitaryTreatment::class, ['activity' => $activity])
            ->set('under_advisory', true)
            ->set('advisory_recommendation_date', $date)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors(['advisory_recommendation_date']);
    }

    // ── applicationsThisCampaign excluye actividad actual ─────────────────────

    public function test_applications_this_campaign_excludes_current_activity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $product       = $this->makeProduct($viticulturist);
        $activity      = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign, $product);

        $this->actingAs($viticulturist);

        // Sin otros tratamientos del mismo producto → debe ser 0
        Livewire::test(EditPhytosanitaryTreatment::class, ['activity' => $activity])
            ->set('campaign_id', $campaign->id)
            ->set('product_id', $product->id)
            ->assertSet('applicationsThisCampaign', 0);
    }

    // ── bloqueo: policy deniega update para actividad bloqueada ─────────────────

    public function test_policy_denies_update_for_locked_activity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $product       = $this->makeProduct($viticulturist);
        $activity      = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign, $product);

        $activity->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $viticulturist->id,
        ]);

        $this->assertFalse(
            $viticulturist->can('update', $activity->fresh())
        );
    }

    // ── autorización: policy deniega update a otro viticulturist ─────────────

    public function test_policy_denies_update_for_other_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $product       = $this->makeProduct($viticulturist);
        $activity      = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign, $product);

        $this->assertFalse(
            $other->can('update', $activity)
        );
    }

    // ── cálculo automático dosis total ────────────────────────────────────────

    public function test_total_dose_recalculated_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $product       = $this->makeProduct($viticulturist);
        $activity      = $this->makeActivityWithTreatment($viticulturist, $plot, $campaign, $product);

        $this->actingAs($viticulturist);

        Livewire::test(EditPhytosanitaryTreatment::class, ['activity' => $activity])
            ->set('dose_per_hectare', 4.0)
            ->set('area_treated', 2.5)
            ->assertSet('total_dose', 10.0);
    }
}
