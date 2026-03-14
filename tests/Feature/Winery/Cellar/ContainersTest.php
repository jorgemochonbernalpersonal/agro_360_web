<?php

namespace Tests\Feature\Winery\Cellar;

use App\Livewire\Winery\Cellar\Containers\Create;
use App\Livewire\Winery\Cellar\Containers\Edit;
use App\Models\Container;
use App\Models\ContainerType;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class ContainersTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_index_renders(): void
    {
        $this->get(route('winery.containers.index'))
            ->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->call('save')
            ->assertHasErrors(['name', 'type_id', 'capacity']);
    }

    public function test_create_saves_container(): void
    {
        $type = ContainerType::firstOrCreate(['name' => 'Depósito Inox']);

        Livewire::test(Create::class)
            ->set('name', 'Cuba 1')
            ->set('type_id', (string) $type->id)
            ->set('capacity', '1000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'user_id' => $this->winery->id,
            'name'    => 'Cuba 1',
            'type_id' => $type->id,
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $type = ContainerType::firstOrCreate(['name' => 'Barrica']);

        $container = Container::create([
            'user_id'       => $this->winery->id,
            'name'          => 'Cuba Vieja',
            'type_id'       => $type->id,
            'capacity'      => 500,
            'used_capacity' => 0,
            'archived'      => false,
        ]);

        Livewire::test(Edit::class, ['container' => $container])
            ->set('name', 'Nuevo Nombre')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'id'   => $container->id,
            'name' => 'Nuevo Nombre',
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $type = ContainerType::firstOrCreate(['name' => 'Depósito']);

        $container = Container::create([
            'user_id'       => $this->winery->id,
            'name'          => 'Cuba Privada',
            'type_id'       => $type->id,
            'capacity'      => 500,
            'used_capacity' => 0,
            'archived'      => false,
        ]);

        $this->actingAs($otherWinery)
            ->get(route('winery.containers.edit', $container))
            ->assertForbidden();
    }
}
