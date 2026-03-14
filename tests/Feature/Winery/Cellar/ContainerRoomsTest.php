<?php

namespace Tests\Feature\Winery\Cellar;

use App\Livewire\Winery\Cellar\ContainerRooms\Create;
use App\Livewire\Winery\Cellar\ContainerRooms\Edit;
use App\Models\ContainerRoom;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class ContainerRoomsTest extends WineryTestCase
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
        $this->get(route('winery.container-rooms.index'))
            ->assertOk();
    }

    public function test_create_validates_required_name(): void
    {
        Livewire::test(Create::class)
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_create_saves_room(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Sala Norte')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('container_rooms', [
            'user_id' => $this->winery->id,
            'name'    => 'Sala Norte',
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $room = ContainerRoom::create([
            'user_id' => $this->winery->id,
            'name'    => 'Test Room',
        ]);

        Livewire::test(Edit::class, ['room' => $room])
            ->set('name', 'Sala Sur')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('container_rooms', [
            'id'   => $room->id,
            'name' => 'Sala Sur',
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $room = ContainerRoom::create([
            'user_id' => $this->winery->id,
            'name'    => 'Sala Privada',
        ]);

        $this->actingAs($otherWinery)
            ->get(route('winery.container-rooms.edit', $room))
            ->assertForbidden();
    }
}
