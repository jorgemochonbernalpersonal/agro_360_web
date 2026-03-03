<?php

namespace Tests\Feature\Viticulturist\EnergyUsages;

use App\Livewire\Viticulturist\EnergyUsages\Index;
use App\Models\Campaign;
use App\Models\EnergyUsage;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    private function makeEnergyUsage(int $viticulturistId, int $campaignId, bool $active = true, string $description = 'Uso test'): EnergyUsage
    {
        return EnergyUsage::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id'      => $campaignId,
            'date'             => '2024-06-15',
            'energy_type'      => 'diesel',
            'unit'             => 'liters',
            'quantity'         => 100,
            'usage_description'=> $description,
            'active'           => $active,
        ]);
    }

    public function test_index_shows_active_usages(): void
    {
        $viticulturist = $this->makeViticulturist();
        // Use same campaign as mount() would create
        $campaign = Campaign::getOrCreateActiveForYear($viticulturist->id);
        $this->makeEnergyUsage($viticulturist->id, $campaign->id, true, 'Consumo-Visible-001');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Consumo-Visible-001');
    }

    public function test_archive_sets_active_to_false(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = Campaign::getOrCreateActiveForYear($viticulturist->id);
        $usage         = $this->makeEnergyUsage($viticulturist->id, $campaign->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('archive', $usage->id);

        $this->assertDatabaseHas('energy_usages', [
            'id'     => $usage->id,
            'active' => false,
        ]);
    }

    public function test_unarchive_restores_record(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = Campaign::getOrCreateActiveForYear($viticulturist->id);
        $usage         = $this->makeEnergyUsage($viticulturist->id, $campaign->id, false);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->call('unarchive', $usage->id);

        $this->assertDatabaseHas('energy_usages', [
            'id'     => $usage->id,
            'active' => true,
        ]);
    }

    public function test_archived_tab_shows_inactive(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = Campaign::getOrCreateActiveForYear($viticulturist->id);
        $this->makeEnergyUsage($viticulturist->id, $campaign->id, false, 'Consumo-Archivado-001');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->assertSee('Consumo-Archivado-001');
    }

    public function test_cannot_archive_other_viticulturists_usage(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $campaign      = Campaign::getOrCreateActiveForYear($viticulturist->id);
        $usage         = $this->makeEnergyUsage($viticulturist->id, $campaign->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('archive', $usage->id);
    }
}
