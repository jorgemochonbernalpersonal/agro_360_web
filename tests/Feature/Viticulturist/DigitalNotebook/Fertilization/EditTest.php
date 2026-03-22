<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Fertilization;

use App\Livewire\Viticulturist\DigitalNotebook\EditFertilization;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Fertilization;
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

    private function makeActivityWithFertilization($viticulturist, Plot $plot, Campaign $campaign): AgriculturalActivity
    {
        $activity = AgriculturalActivity::create([
            'viticulturist_id'   => $viticulturist->id,
            'plot_id'            => $plot->id,
            'campaign_id'        => $campaign->id,
            'activity_type'      => 'fertilization',
            'activity_date'      => now()->format('Y-m-d'),
            'phenological_stage' => 'Brotación',
            'crew_member_id'     => null,
            'is_locked'          => false,
        ]);

        Fertilization::create([
            'activity_id'      => $activity->id,
            'fertilizer_type'  => 'Mineral',
            'fertilizer_name'  => 'NPK 15-15-15',
            'quantity'         => 200.0,
            'area_applied'     => 2.5,
            'npk_ratio'        => '15-15-15',
            'application_method' => 'aplicación al suelo',
            'nitrogen_uf'      => 150.0,
            'phosphorus_uf'    => 100.0,
            'potassium_uf'     => 80.0,
        ]);

        return $activity->load('fertilization');
    }

    // ── mount: campos precargados ──────────────────────────────────────────────

    public function test_mount_fills_all_fields_from_existing_fertilization(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithFertilization($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditFertilization::class, ['activity' => $activity])
            ->assertSet('plot_id', $plot->id)
            ->assertSet('campaign_id', $campaign->id)
            ->assertSet('phenological_stage', 'Brotación')
            ->assertSet('fertilizer_type', 'Mineral')
            ->assertSet('fertilizer_name', 'NPK 15-15-15')
            ->assertSet('npk_ratio', '15-15-15');
    }

    // ── update correcto ────────────────────────────────────────────────────────

    public function test_can_update_fertilization(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithFertilization($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditFertilization::class, ['activity' => $activity])
            ->set('fertilizer_name', 'NPK 20-10-10 Actualizado')
            ->set('quantity', 250)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.digital-notebook.fertilization.index'));

        $this->assertDatabaseHas('fertilizations', [
            'activity_id'     => $activity->id,
            'fertilizer_name' => 'NPK 20-10-10 Actualizado',
        ]);
    }

    // ── validaciones en edición ────────────────────────────────────────────────

    public function test_fertilizer_type_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithFertilization($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditFertilization::class, ['activity' => $activity])
            ->set('fertilizer_type', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['fertilizer_type']);
    }

    public function test_all_npk_empty_fails_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithFertilization($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditFertilization::class, ['activity' => $activity])
            ->set('nitrogen_uf', '')
            ->set('phosphorus_uf', '')
            ->set('potassium_uf', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['nitrogen_uf']);
    }

    // ── PAC: actualizar campos NPK ────────────────────────────────────────────

    public function test_can_update_npk_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithFertilization($viticulturist, $plot, $campaign);

        $this->actingAs($viticulturist);

        Livewire::test(EditFertilization::class, ['activity' => $activity])
            ->set('nitrogen_uf', 200.5)
            ->set('phosphorus_uf', 120.0)
            ->set('potassium_uf', 90.0)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $fertilization = Fertilization::where('activity_id', $activity->id)->first();
        $this->assertEquals('200.500', $fertilization->nitrogen_uf);
        $this->assertEquals('120.000', $fertilization->phosphorus_uf);
    }

    // ── bloqueo ────────────────────────────────────────────────────────────────

    public function test_policy_denies_update_for_locked_activity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $activity      = $this->makeActivityWithFertilization($viticulturist, $plot, $campaign);

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
        $activity      = $this->makeActivityWithFertilization($viticulturist, $plot, $campaign);

        $this->assertFalse(
            $other->can('update', $activity)
        );
    }
}
