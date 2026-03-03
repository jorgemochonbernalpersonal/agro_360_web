<?php

namespace Tests\Feature\Viticulturist\ResidueManagements;

use App\Livewire\Viticulturist\ResidueManagements\Index;
use App\Models\Campaign;
use App\Models\ResidueManagement;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UnitSeeder::class);
    }

    private function makeManagement(int $viticulturistId, string $practiceType = 'incorporation', string $notes = ''): ResidueManagement
    {
        $campaign = Campaign::getOrCreateActiveForYear($viticulturistId);

        return ResidueManagement::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id'      => $campaign->id,
            'date'             => '2024-03-15',
            'practice_type'    => $practiceType,
            'material_type'    => 'pruning_wood',
            'notes'            => $notes ?: null,
            'active'           => true,
        ]);
    }

    public function test_index_shows_active_managements(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->makeManagement($viticulturist->id, 'incorporation');

        $this->actingAs($viticulturist);

        // The index renders the practice type label
        Livewire::test(Index::class)
            ->assertSee('Triturado e incorporación al suelo');
    }

    public function test_deactivate_archives_management(): void
    {
        $viticulturist = $this->makeViticulturist();
        $management    = $this->makeManagement($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('deactivate', $management->id);

        $this->assertDatabaseHas('residue_managements', [
            'id'     => $management->id,
            'active' => false,
        ]);
    }

    public function test_filter_by_practice_type(): void
    {
        $viticulturist = $this->makeViticulturist();
        $campaign      = Campaign::getOrCreateActiveForYear($viticulturist->id);

        // Use different material_types so we can distinguish rows by their label
        // (practiceTypes are in the filter dropdown, materialTypes are NOT → safe for assertSee/assertDontSee)
        ResidueManagement::create([
            'viticulturist_id' => $viticulturist->id,
            'campaign_id'      => $campaign->id,
            'date'             => '2024-03-15',
            'practice_type'    => 'composting',
            'material_type'    => 'grape_marc',   // "Orujo"
            'active'           => true,
        ]);
        ResidueManagement::create([
            'viticulturist_id' => $viticulturist->id,
            'campaign_id'      => $campaign->id,
            'date'             => '2024-03-15',
            'practice_type'    => 'removal',
            'material_type'    => 'vine_leaves',  // "Hojas de vid"
            'active'           => true,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('filterPractice', 'composting')
            ->assertSee('Orujo')
            ->assertDontSee('Hojas de vid');
    }

    public function test_cannot_deactivate_other_viticulturists_management(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other         = $this->makeOtherViticulturist();
        $management    = $this->makeManagement($viticulturist->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('deactivate', $management->id);
    }
}
