<?php

namespace Tests\Feature\Viticulturist\ContainerReturns;

use App\Livewire\Viticulturist\ContainerReturns\Edit;
use App\Models\Campaign;
use App\Models\PhytosanitaryContainerReturn;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $return = $this->makeReturn($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['containerReturn' => $return])
            ->assertSet('product_name', 'Envase Test')
            ->assertSet('containers_quantity', '3')
            ->assertSet('collection_point', 'Punto Recogida Test')
            ->assertSet('container_type', 'plastic');
    }

    public function test_can_update_container_return(): void
    {
        $viticulturist = $this->makeViticulturist();
        $return = $this->makeReturn($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['containerReturn' => $return])
            ->set('product_name', 'Envase Actualizado')
            ->set('containers_quantity', '10')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.container-returns.index'));

        $this->assertDatabaseHas('phytosanitary_container_returns', [
            'id' => $return->id,
            'product_name' => 'Envase Actualizado',
            'containers_quantity' => 10,
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $viticulturist = $this->makeViticulturist();
        $return = $this->makeReturn($viticulturist->id);

        $this->actingAs($viticulturist);

        Livewire::test(Edit::class, ['containerReturn' => $return])
            ->set('product_name', '')
            ->set('containers_quantity', '')
            ->set('collection_point', '')
            ->call('save')
            ->assertHasErrors(['product_name', 'containers_quantity', 'collection_point']);
    }

    public function test_cannot_edit_other_viticulturists_return(): void
    {
        $viticulturist = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $return = $this->makeReturn($viticulturist->id);

        $this->actingAs($other)
            ->get(route('viticulturist.container-returns.edit', $return))
            ->assertStatus(403);
    }

    private function makeReturn(int $viticulturistId): PhytosanitaryContainerReturn
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
            'product_name' => 'Envase Test',
            'container_type' => 'plastic',
            'containers_quantity' => 3,
            'collection_system' => 'sigfito',
            'collection_point' => 'Punto Recogida Test',
            'active' => true,
        ]);
    }
}
