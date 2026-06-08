<?php

namespace Tests\Feature\Viticulturist\ContainerReturns;

use App\Livewire\Viticulturist\ContainerReturns\Create;
use App\Models\PhytosanitaryContainerReturn;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateTest extends ViticulturistTestCase
{
    public function test_can_create_container_return(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('product_name', 'Roundup 360')
            ->set('containers_quantity', '5')
            ->set('collection_point', 'Punto recogida Almería')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('viticulturist.container-returns.index'));

        $this->assertDatabaseHas('phytosanitary_container_returns', [
            'viticulturist_id' => $viticulturist->id,
            'product_name' => 'Roundup 360',
            'containers_quantity' => 5,
            'container_type' => 'plastic',
            'collection_system' => 'sigfito',
            'active' => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('campaign_id', '')
            ->set('product_name', '')
            ->set('containers_quantity', '')
            ->set('collection_point', '')
            ->call('save')
            ->assertHasErrors(['campaign_id', 'product_name', 'containers_quantity', 'collection_point']);
    }

    public function test_containers_quantity_must_be_at_least_one(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->set('product_name', 'Test Product')
            ->set('containers_quantity', '0')
            ->set('collection_point', 'Punto A')
            ->call('save')
            ->assertHasErrors(['containers_quantity']);
    }

    public function test_mount_sets_today_as_date(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertSet('date', now()->format('Y-m-d'));
    }

    public function test_mount_auto_sets_campaign_id(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Create::class)
            ->assertNotSet('campaign_id', '');
    }
}
