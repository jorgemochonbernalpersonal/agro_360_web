<?php

namespace Tests\Feature\Winery\Cellar;

use App\Livewire\Winery\Cellar\Containers\Create;
use App\Livewire\Winery\Cellar\Containers\Edit;
use App\Models\Container;
use App\Models\ContainerRoom;
use App\Models\ContainerType;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ProducerTestCase;

/**
 * Producer usa los mismos componentes Livewire que Winery
 * (App\Livewire\Winery\Cellar\Containers\*).
 *
 * Estos tests verifican:
 *  - Las rutas producer.containers.* son accesibles para un producer.
 *  - El aislamiento de datos: un producer no puede ver ni editar contenedores de otro usuario.
 *  - La asignación de sala respeta el propietario del producer.
 *
 * La lógica de negocio (validaciones, guardado, unidades) está cubierta
 * exhaustivamente en ContainersTest (WineryTestCase) ya que comparte componentes.
 */
class ProducerContainersTest extends ProducerTestCase
{
    private User $producer;

    private ContainerType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->producer = $this->makeProducer();
        $this->actingAs($this->producer);
        $this->type = ContainerType::firstOrCreate(['name' => 'Depósito Inox']);
    }

    // ── Rutas accesibles ──────────────────────────────────────────────────────

    public function test_producer_can_access_containers_index(): void
    {
        $this->get(route('producer.containers.index'))->assertOk();
    }

    public function test_producer_can_access_containers_create(): void
    {
        $this->get(route('producer.containers.create'))->assertOk();
    }

    public function test_producer_can_access_container_rooms_index(): void
    {
        $this->get(route('producer.container-rooms.index'))->assertOk();
    }

    // ── Create funciona para producer ─────────────────────────────────────────

    public function test_producer_can_create_container(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba Producer')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->set('unit', 'kg')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'user_id' => $this->producer->id,
            'name' => 'Cuba Producer',
        ]);
    }

    public function test_producer_can_create_container_with_room(): void
    {
        $room = ContainerRoom::create([
            'user_id' => $this->producer->id,
            'name' => 'Sala Producer',
        ]);

        Livewire::test(Create::class)
            ->set('name', 'Cuba en Sala Producer')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '800')
            ->set('unit', 'litros')
            ->set('container_room_id', (string) $room->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'user_id' => $this->producer->id,
            'name' => 'Cuba en Sala Producer',
            'container_room_id' => $room->id,
        ]);
    }

    // ── Aislamiento entre usuarios ────────────────────────────────────────────

    public function test_producer_cannot_access_edit_of_another_users_container(): void
    {
        $otherWinery = $this->makeWinery();
        $foreignContainer = Container::create([
            'user_id' => $otherWinery->id,
            'name' => 'Cuba Ajena',
            'type_id' => $this->type->id,
            'capacity' => 500,
            'unit' => 'kg',
            'used_capacity' => 0,
            'archived' => false,
        ]);

        // La ruta producer usa el mismo componente Edit que verifica user_id
        $this->get(route('producer.containers.edit', $foreignContainer))
            ->assertForbidden();
    }

    public function test_producer_cannot_edit_another_users_container_via_livewire(): void
    {
        $otherProducer = $this->makeOtherProducer();
        $foreignContainer = Container::create([
            'user_id' => $otherProducer->id,
            'name' => 'Cuba Ajena Livewire',
            'type_id' => $this->type->id,
            'capacity' => 500,
            'unit' => 'kg',
            'used_capacity' => 0,
            'archived' => false,
        ]);

        Livewire::test(Edit::class, ['container' => $foreignContainer])
            ->assertStatus(403);
    }

    public function test_producer_cannot_assign_room_from_another_user(): void
    {
        $otherProducer = $this->makeOtherProducer();
        $foreignRoom = ContainerRoom::create([
            'user_id' => $otherProducer->id,
            'name' => 'Sala Ajena',
        ]);

        Livewire::test(Create::class)
            ->set('name', 'Cuba Intrusión')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->set('container_room_id', (string) $foreignRoom->id)
            ->call('save')
            ->assertHasErrors(['container_room_id']);
    }

    public function test_producer_edit_cannot_assign_room_from_another_user(): void
    {
        $otherProducer = $this->makeOtherProducer();
        $foreignRoom = ContainerRoom::create([
            'user_id' => $otherProducer->id,
            'name' => 'Sala Ajena Edit',
        ]);
        $container = $this->makeContainer();

        Livewire::test(Edit::class, ['container' => $container])
            ->set('container_room_id', (string) $foreignRoom->id)
            ->call('save')
            ->assertHasErrors(['container_room_id']);
    }

    // ── Edit funciona para producer ───────────────────────────────────────────

    public function test_producer_can_edit_own_container(): void
    {
        $container = $this->makeContainer(['name' => 'Cuba Vieja Producer']);

        Livewire::test(Edit::class, ['container' => $container])
            ->set('name', 'Cuba Renovada Producer')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'id' => $container->id,
            'name' => 'Cuba Renovada Producer',
        ]);
    }

    public function test_producer_can_assign_and_remove_room(): void
    {
        $room = ContainerRoom::create(['user_id' => $this->producer->id, 'name' => 'Sala Edit Producer']);
        $container = $this->makeContainer();

        // Asignar sala
        Livewire::test(Edit::class, ['container' => $container])
            ->set('container_room_id', (string) $room->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', ['id' => $container->id, 'container_room_id' => $room->id]);

        // Quitar sala
        Livewire::test(Edit::class, ['container' => $container->fresh()])
            ->set('container_room_id', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', ['id' => $container->id, 'container_room_id' => null]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeContainer(array $attrs = []): Container
    {
        return Container::create(array_merge([
            'user_id' => $this->producer->id,
            'name' => 'Cuba Producer',
            'type_id' => $this->type->id,
            'capacity' => 1000,
            'unit' => 'kg',
            'used_capacity' => 0,
            'archived' => false,
        ], $attrs));
    }
}
