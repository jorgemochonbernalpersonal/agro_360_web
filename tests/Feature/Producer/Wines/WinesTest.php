<?php

namespace Tests\Feature\Producer\Wines;

use App\Livewire\Winery\Wines\Create;
use App\Livewire\Winery\Wines\Edit;
use App\Models\User;
use App\Models\Wine;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

class WinesTest extends ProducerTestCase
{
    private User $producer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->producer = $this->makeProducer();
        $this->actingAs($this->producer);
    }

    public function test_index_renders(): void
    {
        $this->get(route('producer.wines.index'))->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('name', '')
            ->set('wine_type', '')
            ->set('status', '')
            ->call('save')
            ->assertHasErrors(['name', 'wine_type', 'status']);
    }

    public function test_create_saves_wine(): void
    {
        $firstType = array_key_first(Wine::WINE_TYPES);
        $firstStatus = array_key_first(Wine::STATUSES);

        Livewire::test(Create::class)
            ->set('name', 'Garnacha Producer 2024')
            ->set('wine_type', $firstType)
            ->set('status', $firstStatus)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wines', [
            'user_id' => $this->producer->id,
            'name' => 'Garnacha Producer 2024',
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $wine = Wine::create([
            'user_id' => $this->producer->id,
            'name' => 'Old Name',
            'wine_type' => array_key_first(Wine::WINE_TYPES),
            'status' => array_key_first(Wine::STATUSES),
        ]);

        Livewire::test(Edit::class, ['wine' => $wine])
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wines', ['id' => $wine->id, 'name' => 'New Name']);
    }

    public function test_other_producer_cannot_edit(): void
    {
        $wine = Wine::create([
            'user_id' => $this->producer->id,
            'name' => 'Protected Wine',
            'wine_type' => array_key_first(Wine::WINE_TYPES),
            'status' => array_key_first(Wine::STATUSES),
        ]);

        $this->actingAs($this->makeOtherProducer())
            ->get(route('producer.wines.edit', $wine))
            ->assertForbidden();
    }

    public function test_process_edit_route_exists(): void
    {
        $wine = Wine::create([
            'user_id' => $this->producer->id,
            'name' => 'Process Wine',
            'wine_type' => array_key_first(Wine::WINE_TYPES),
            'status' => array_key_first(Wine::STATUSES),
        ]);

        // Verify the route is registered (process edit was missing before fix)
        $this->assertNotNull(route('producer.wines.process.edit', ['wine' => $wine, 'process' => 1]));
    }
}
