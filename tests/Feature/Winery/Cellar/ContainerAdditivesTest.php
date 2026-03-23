<?php

namespace Tests\Feature\Winery\Cellar;

use App\Livewire\Winery\Cellar\Containers\Additives\Create;
use App\Livewire\Winery\Cellar\Containers\Additives\Edit;
use App\Livewire\Winery\Cellar\Containers\Additives\Index;
use App\Models\Container;
use App\Models\ContainerAdditiveSupply;
use App\Models\ContainerType;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

/**
 * Tests for Winery Container Additives module.
 *
 * Covers: Index renders, Create (happy path, validation),
 *         Edit (happy path, authorization), delete.
 */
class ContainerAdditivesTest extends WineryTestCase
{
    private User      $winery;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);

        $type = ContainerType::firstOrCreate(['name' => 'Depósito Inox']);

        $this->container = Container::create([
            'user_id'       => $this->winery->id,
            'name'          => 'Cuba Aditivos',
            'type_id'       => $type->id,
            'capacity'      => 5000,
            'unit'          => 'litros',
            'used_capacity' => 0,
            'archived'      => false,
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function makeAdditive(array $attrs = []): ContainerAdditiveSupply
    {
        return ContainerAdditiveSupply::create(array_merge([
            'container_id'  => $this->container->id,
            'additive_name' => 'SO2',
            'quantity'      => 5.5,
            'additive_date' => now()->toDateString(),
            'created_by'    => $this->winery->id,
        ], $attrs));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_index_renders(): void
    {
        $this->get(route('winery.containers.additives.index', $this->container))
            ->assertOk();
    }

    public function test_other_winery_cannot_view_additives(): void
    {
        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.containers.additives.index', $this->container))
            ->assertForbidden();
    }

    // ── Create — happy path ───────────────────────────────────────────────────

    public function test_can_create_additive(): void
    {
        Livewire::test(Create::class, ['container' => $this->container])
            ->set('additive_name', 'Bentonita')
            ->set('quantity', '3.5')
            ->set('additive_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('container_additive_supplies', [
            'container_id'  => $this->container->id,
            'additive_name' => 'Bentonita',
            'quantity'      => 3.5,
        ]);
    }

    // ── Create — validation ───────────────────────────────────────────────────

    public function test_create_validates_quantity_is_required(): void
    {
        Livewire::test(Create::class, ['container' => $this->container])
            ->set('additive_name', 'Test')
            ->set('quantity', '')
            ->set('additive_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['quantity']);
    }

    public function test_create_validates_quantity_greater_than_zero(): void
    {
        Livewire::test(Create::class, ['container' => $this->container])
            ->set('additive_name', 'Test')
            ->set('quantity', '0')
            ->set('additive_date', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['quantity']);
    }

    public function test_create_validates_date_required(): void
    {
        Livewire::test(Create::class, ['container' => $this->container])
            ->set('additive_name', 'Test')
            ->set('quantity', '1.0')
            ->set('additive_date', '')
            ->call('save')
            ->assertHasErrors(['additive_date']);
    }

    // ── Edit — happy path ─────────────────────────────────────────────────────

    public function test_can_edit_additive(): void
    {
        $additive = $this->makeAdditive(['additive_name' => 'Old Name', 'quantity' => 2.0]);

        Livewire::test(Edit::class, ['container' => $this->container, 'additive' => $additive])
            ->set('additive_name', 'Lisozima')
            ->set('quantity', '4.0')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('container_additive_supplies', [
            'id'            => $additive->id,
            'additive_name' => 'Lisozima',
            'quantity'      => 4.0,
        ]);
    }

    // ── Edit — authorization ──────────────────────────────────────────────────

    public function test_other_winery_cannot_edit_additive(): void
    {
        $additive = $this->makeAdditive();

        $this->actingAs($this->makeOtherWinery())
            ->get(route('winery.containers.additives.edit', [$this->container, $additive]))
            ->assertForbidden();
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function test_can_delete_additive(): void
    {
        $additive = $this->makeAdditive();

        Livewire::test(Index::class, ['container' => $this->container])
            ->call('delete', $additive->id);

        $this->assertDatabaseMissing('container_additive_supplies', ['id' => $additive->id]);
    }

    public function test_cannot_delete_additive_of_another_container(): void
    {
        $type            = ContainerType::firstOrCreate(['name' => 'Depósito Inox']);
        $otherContainer  = Container::create([
            'user_id'       => $this->winery->id,
            'name'          => 'Cuba Ajena',
            'type_id'       => $type->id,
            'capacity'      => 1000,
            'unit'          => 'litros',
            'used_capacity' => 0,
            'archived'      => false,
        ]);

        $additive = ContainerAdditiveSupply::create([
            'container_id'  => $otherContainer->id,
            'additive_name' => 'Foreign',
            'quantity'      => 1.0,
            'additive_date' => now()->toDateString(),
            'created_by'    => $this->winery->id,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Index is scoped to $this->container, so additive of another container is not found
        Livewire::test(Index::class, ['container' => $this->container])
            ->call('delete', $additive->id);
    }
}
