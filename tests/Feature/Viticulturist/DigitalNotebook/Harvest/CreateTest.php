<?php

namespace Tests\Feature\Viticulturist\DigitalNotebook\Harvest;

use App\Livewire\Viticulturist\DigitalNotebook\CreateHarvest;
use App\Models\AgriculturalActivity;
use App\Models\AutonomousCommunity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\PlotPlanting;
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

    private function makePlanting(Plot $plot): PlotPlanting
    {
        return PlotPlanting::create([
            'plot_id'      => $plot->id,
            'area_planted' => 2.5,
            'status'       => 'active',
        ]);
    }

    // ── mount ──────────────────────────────────────────────────────────────────

    public function test_component_renders(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateHarvest::class)
            ->assertOk();
    }

    public function test_mount_sets_today_as_activity_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateHarvest::class)
            ->assertSet('activity_date', now()->format('Y-m-d'));
    }

    public function test_mount_sets_today_as_harvest_start_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateHarvest::class)
            ->assertSet('harvest_start_date', now()->format('Y-m-d'));
    }

    public function test_mount_sets_campaign_id(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);

        $this->actingAs($viticulturist);

        Livewire::test(CreateHarvest::class)
            ->assertSet('campaign_id', $campaign->id);
    }

    // ── save correcto ──────────────────────────────────────────────────────────

    public function test_can_save_harvest(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        // Second planting to avoid auto-select triggering loadControlPanelData
        $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(CreateHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('harvest_start_date', now()->format('Y-m-d'))
            ->set('total_weight', 2500.0)
            ->set('destination_type', 'self_consumption')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('harvests', [
            'total_weight' => 2500.0,
            'status'       => 'active',
        ]);
    }

    public function test_can_save_with_potential_alcohol(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(CreateHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('harvest_start_date', now()->format('Y-m-d'))
            ->set('total_weight', 2000.0)
            ->set('potential_alcohol', 13.5)
            ->set('destination_type', 'self_consumption')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $harvest = Harvest::latest()->first();
        $this->assertEquals('13.50', $harvest->potential_alcohol);
    }

    public function test_can_save_with_transport_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(CreateHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('harvest_start_date', now()->format('Y-m-d'))
            ->set('total_weight', 3000.0)
            ->set('destination_type', 'winery')
            ->set('transport_document_number', 'GUIA-2026-001')
            ->set('destination_rega_code', 'ES010000000001')
            ->set('vehicle_plate', '1234 ABC')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors();

        $harvest = Harvest::latest()->first();
        $this->assertEquals('GUIA-2026-001', $harvest->transport_document_number);
        $this->assertEquals('ES010000000001', $harvest->destination_rega_code);
        $this->assertEquals('1234 ABC', $harvest->vehicle_plate);
    }

    // ── validaciones ──────────────────────────────────────────────────────────

    public function test_total_weight_required(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(CreateHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('harvest_start_date', now()->format('Y-m-d'))
            ->set('total_weight', '')
            ->set('destination_type', 'self_consumption')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['total_weight']);
    }

    public function test_potential_alcohol_max_25(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(CreateHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('harvest_start_date', now()->format('Y-m-d'))
            ->set('total_weight', 1000.0)
            ->set('potential_alcohol', 30)
            ->set('destination_type', 'self_consumption')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['potential_alcohol']);
    }

    public function test_transport_document_required_unless_self_consumption(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(CreateHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('harvest_start_date', now()->format('Y-m-d'))
            ->set('total_weight', 1000.0)
            ->set('destination_type', 'winery')
            ->set('transport_document_number', '')
            ->set('destination_rega_code', 'ES010000000001')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasErrors(['transport_document_number']);
    }

    public function test_transport_document_not_required_for_self_consumption(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($viticulturist);
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        Livewire::test(CreateHarvest::class)
            ->set('plot_id', $plot->id)
            ->set('plot_planting_id', $planting->id)
            ->set('harvest_start_date', now()->format('Y-m-d'))
            ->set('total_weight', 1000.0)
            ->set('destination_type', 'self_consumption')
            ->set('transport_document_number', '')
            ->set('destination_rega_code', '')
            ->set('workType', 'individual')
            ->set('crew_member_id', $viticulturist->id)
            ->call('save')
            ->assertHasNoErrors(['transport_document_number', 'destination_rega_code']);
    }

    // ── autorización ──────────────────────────────────────────────────────────

    public function test_policy_denies_plot_from_other_viticulturist(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $campaign      = $this->makeCampaign($viticulturist);
        $plot          = $this->makePlot($other);  // plot belongs to other
        $planting      = $this->makePlanting($plot);
        $this->makePlanting($plot);

        $this->actingAs($viticulturist);

        $this->assertFalse(
            $viticulturist->can('update', $plot)
        );
    }
}
