<?php

namespace Tests\Feature\Viticulturist\ContainerReturns;

use App\Livewire\Viticulturist\ContainerReturns\Index;
use App\Models\Campaign;
use App\Models\PhytosanitaryContainerReturn;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_index_shows_active_returns(): void
    {
        $viticulturist = $this->makeViticulturist();
        $return = $this->makeReturn($viticulturist->id, true, 'Herbicida-Visible-001');

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('filterCampaign', '')
            ->assertSee('Herbicida-Visible-001');
    }

    public function test_archive_sets_active_to_false(): void
    {
        $viticulturist = $this->makeViticulturist();
        $return = $this->makeReturn($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('archive', $return->id);

        $this->assertDatabaseHas('phytosanitary_container_returns', [
            'id' => $return->id,
            'active' => false,
        ]);
    }

    public function test_unarchive_restores_return(): void
    {
        $viticulturist = $this->makeViticulturist();
        $return = $this->makeReturn($viticulturist->id, false);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('switchTab', 'archived')
            ->call('unarchive', $return->id);

        $this->assertDatabaseHas('phytosanitary_container_returns', [
            'id' => $return->id,
            'active' => true,
        ]);
    }

    public function test_delete_removes_return(): void
    {
        $viticulturist = $this->makeViticulturist();
        $return = $this->makeReturn($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $return->id);

        $this->assertDatabaseMissing('phytosanitary_container_returns', ['id' => $return->id]);
    }

    public function test_cannot_archive_other_viticulturists_return(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $return = $this->makeReturn($viticulturist->id);

        $this->actingAs($other);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class)
            ->call('archive', $return->id);
    }

    private function makeReturn(int $viticulturistId, bool $active = true, string $productName = 'Envase Test'): PhytosanitaryContainerReturn
    {
        $campaign = Campaign::create([
            'viticulturist_id' => $viticulturistId,
            'year' => 2024,
            'name' => 'Campaña 2024',
        ]);

        return PhytosanitaryContainerReturn::create([
            'viticulturist_id' => $viticulturistId,
            'campaign_id' => $campaign->id,
            'date' => '2024-06-15',
            'product_name' => $productName,
            'container_type' => 'plastic',
            'containers_quantity' => 3,
            'collection_system' => 'sigfito',
            'collection_point' => 'Punto Recogida Test',
            'active' => $active,
        ]);
    }
}
