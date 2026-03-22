<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Harvest;

use App\Livewire\Viticulturist\DigitalNotebook\EditHarvest;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\Province;
use App\Models\WineryViticulturist;
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

    private function makePlanting(Plot $plot): PlotPlanting
    {
        return PlotPlanting::create([
            'plot_id'       => $plot->id,
            'area_planted'  => 2.5,
            'status'        => 'active',
        ]);
    }

    private function makeActivityWithHarvest($viticulturist, Plot $plot, Campaign $campaign, PlotPlanting $planting): AgriculturalActivity
    {
        $activity = AgriculturalActivity::create([
            'viticulturist_id' => $viticulturist->id,
            'plot_id'          => $plot->id,
            'campaign_id'      => $campaign->id,
            'activity_type'    => 'harvest',
            'activity_date'    => now()->format('Y-m-d'),
            'is_locked'        => false,
        ]);

        Harvest::withoutEvents(fn () => Harvest::create([
            'activity_id'              => $activity->id,
            'plot_planting_id'         => $planting->id,
            'harvest_start_date'       => now()->format('Y-m-d'),
            'total_weight'             => 2500.0,
            'destination_type'         => 'winery',
            'transport_document_number'=> 'GUIA-2026-001',
            'destination_rega_code'    => 'ES010000000001',
            'vehicle_plate'            => '1234 ABC',
            'potential_alcohol'        => 13.5,
            'status'                   => 'active',
        ]));

        return $activity->load('harvest');
    }

    // ── mount: campos precargados ──────────────────────────────────────────────

    public function test_mount_fills_basic_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        // Second planting to avoid auto-select control panel loading
        $this->makePlanting($plot);
        $activity      = $this->makeActivityWithHarvest($viticulturist, $plot, $campaign, $planting);

        $this->actingAs($viticulturist);

        Livewire::test(EditHarvest::class, ['harvest' => $activity->harvest->id])
            ->assertSet('plot_id', $plot->id)
            ->assertSet('destination_type', 'winery')
            ->assertSet('total_weight', '2500.000');
    }

    public function test_mount_fills_transport_document_number(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);
        $activity      = $this->makeActivityWithHarvest($viticulturist, $plot, $campaign, $planting);

        $this->actingAs($viticulturist);

        Livewire::test(EditHarvest::class, ['harvest' => $activity->harvest->id])
            ->assertSet('transport_document_number', 'GUIA-2026-001')
            ->assertSet('destination_rega_code', 'ES010000000001')
            ->assertSet('vehicle_plate', '1234 ABC');
    }

    public function test_mount_fills_potential_alcohol(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);
        $activity      = $this->makeActivityWithHarvest($viticulturist, $plot, $campaign, $planting);

        $this->actingAs($viticulturist);

        Livewire::test(EditHarvest::class, ['harvest' => $activity->harvest->id])
            ->assertSet('potential_alcohol', '13.50');
    }

    // ── update correcto ────────────────────────────────────────────────────────

    public function test_can_update_harvest(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);
        $activity      = $this->makeActivityWithHarvest($viticulturist, $plot, $campaign, $planting);

        $this->actingAs($viticulturist);

        Livewire::test(EditHarvest::class, ['harvest' => $activity->harvest->id])
            ->set('total_weight', 3000.0)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvests', [
            'id'           => $activity->harvest->id,
            'total_weight' => 3000.0,
        ]);
    }

    // ── update trazabilidad fields ────────────────────────────────────────────

    public function test_can_update_transport_document_number(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);
        $activity      = $this->makeActivityWithHarvest($viticulturist, $plot, $campaign, $planting);

        $this->actingAs($viticulturist);

        Livewire::test(EditHarvest::class, ['harvest' => $activity->harvest->id])
            ->set('transport_document_number', 'GUIA-2026-999')
            ->set('destination_rega_code', 'ES999999999999')
            ->set('vehicle_plate', '9999 ZZZ')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $harvest = Harvest::find($activity->harvest->id);
        $this->assertEquals('GUIA-2026-999', $harvest->transport_document_number);
        $this->assertEquals('ES999999999999', $harvest->destination_rega_code);
        $this->assertEquals('9999 ZZZ', $harvest->vehicle_plate);
    }

    // ── update potential_alcohol ──────────────────────────────────────────────

    public function test_can_update_potential_alcohol(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);
        $activity      = $this->makeActivityWithHarvest($viticulturist, $plot, $campaign, $planting);

        $this->actingAs($viticulturist);

        Livewire::test(EditHarvest::class, ['harvest' => $activity->harvest->id])
            ->set('potential_alcohol', 14.5)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $harvest = Harvest::find($activity->harvest->id);
        $this->assertEquals('14.50', $harvest->potential_alcohol);
    }

    public function test_can_clear_potential_alcohol_to_null(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);
        $activity      = $this->makeActivityWithHarvest($viticulturist, $plot, $campaign, $planting);

        $this->actingAs($viticulturist);

        Livewire::test(EditHarvest::class, ['harvest' => $activity->harvest->id])
            ->set('potential_alcohol', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasNoErrors();

        $harvest = Harvest::find($activity->harvest->id);
        $this->assertNull($harvest->potential_alcohol);
    }

    // ── validaciones ──────────────────────────────────────────────────────────

    public function test_total_weight_required_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);
        $activity      = $this->makeActivityWithHarvest($viticulturist, $plot, $campaign, $planting);

        $this->actingAs($viticulturist);

        Livewire::test(EditHarvest::class, ['harvest' => $activity->harvest->id])
            ->set('total_weight', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['total_weight']);
    }

    public function test_potential_alcohol_max_25_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);
        $activity      = $this->makeActivityWithHarvest($viticulturist, $plot, $campaign, $planting);

        $this->actingAs($viticulturist);

        Livewire::test(EditHarvest::class, ['harvest' => $activity->harvest->id])
            ->set('potential_alcohol', 30)
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('update')
            ->assertHasErrors(['potential_alcohol']);
    }

    // ── bloqueo ────────────────────────────────────────────────────────────────

    public function test_policy_denies_update_for_locked_activity(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);
        $activity      = $this->makeActivityWithHarvest($viticulturist, $plot, $campaign, $planting);

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
        $other         = $this->makeOtherViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);
        $activity      = $this->makeActivityWithHarvest($viticulturist, $plot, $campaign, $planting);

        $this->assertFalse(
            $other->can('update', $activity)
        );
    }
}
