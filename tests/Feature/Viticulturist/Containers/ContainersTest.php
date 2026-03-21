<?php

namespace Tests\Feature\Viticulturist\Containers;

use App\Livewire\Viticulturist\Containers\Create;
use App\Livewire\Viticulturist\Containers\Edit;
use App\Livewire\Viticulturist\Containers\Index;
use App\Models\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\ContainerMaterial;
use App\Models\ContainerRoom;
use App\Models\ContainerType;
use App\Models\UnitOfMeasurement;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

/**
 * Tests para los componentes propios del viticulturist:
 *   App\Livewire\Viticulturist\Containers\Create
 *   App\Livewire\Viticulturist\Containers\Edit
 *
 * Campos obligatorios propios: material_id, unit_of_measurement_id
 * Diferencias respecto a Winery: sin enum unit, con quantity/oak_type/toast_type/photos
 * Auth en Edit: findOrFail con user_id → 404 para contenedores ajenos
 * Bug corregido: container_room_id ahora valida propietario con Rule::exists
 */
class ContainersTest extends ViticulturistTestCase
{
    private User $viticulturist;
    private ContainerType $type;
    private ContainerMaterial $material;
    private UnitOfMeasurement $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viticulturist = $this->makeViticulturist();
        $this->actingAs($this->viticulturist);

        $this->type     = ContainerType::firstOrCreate(['name' => 'Barrica']);
        $this->material = ContainerMaterial::firstOrCreate(['name' => 'Acero Inoxidable'], ['description' => 'Acero 304']);
        $this->unit     = UnitOfMeasurement::firstOrCreate(['name' => 'Litros'], ['symbol' => 'L', 'type' => 'volume']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeContainer(array $attrs = []): Container
    {
        return Container::create(array_merge([
            'user_id'               => $this->viticulturist->id,
            'name'                  => 'Cuba Viti Base',
            'type_id'               => $this->type->id,
            'material_id'           => $this->material->id,
            'unit_of_measurement_id' => $this->unit->id,
            'capacity'              => 500,
            'used_capacity'         => 0,
            'archived'              => false,
        ], $attrs));
    }

    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function test_index_renders(): void
    {
        // Usamos Livewire::test() para evitar el middleware require.complete (suscripción)
        // que bloquea el acceso HTTP a usuarios sin plan completo
        Livewire::test(Index::class)->assertOk();
    }

    // ── CREATE — validaciones ─────────────────────────────────────────────────

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->call('save')
            ->assertHasErrors(['name', 'type_id', 'material_id', 'unit_of_measurement_id', 'capacity']);
    }

    public function test_create_validates_name_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('type_id', (string) $this->type->id)
            ->set('material_id', (string) $this->material->id)
            ->set('unit_of_measurement_id', (string) $this->unit->id)
            ->set('capacity', '500')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_create_validates_material_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('unit_of_measurement_id', (string) $this->unit->id)
            ->set('capacity', '500')
            ->call('save')
            ->assertHasErrors(['material_id']);
    }

    public function test_create_validates_unit_of_measurement_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('material_id', (string) $this->material->id)
            ->set('capacity', '500')
            ->call('save')
            ->assertHasErrors(['unit_of_measurement_id']);
    }

    public function test_create_validates_capacity_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('material_id', (string) $this->material->id)
            ->set('unit_of_measurement_id', (string) $this->unit->id)
            ->call('save')
            ->assertHasErrors(['capacity']);
    }

    public function test_create_validates_capacity_minimum_one(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('material_id', (string) $this->material->id)
            ->set('unit_of_measurement_id', (string) $this->unit->id)
            ->set('capacity', '0')
            ->call('save')
            ->assertHasErrors(['capacity']);
    }

    public function test_create_validates_toast_type_enum(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('material_id', (string) $this->material->id)
            ->set('unit_of_measurement_id', (string) $this->unit->id)
            ->set('capacity', '500')
            ->set('toast_type', 'extra_heavy')   // valor no válido
            ->call('save')
            ->assertHasErrors(['toast_type']);
    }

    public function test_create_validates_purchase_date_not_future(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('material_id', (string) $this->material->id)
            ->set('unit_of_measurement_id', (string) $this->unit->id)
            ->set('capacity', '500')
            ->set('purchase_date', '2099-01-01')
            ->call('save')
            ->assertHasErrors(['purchase_date']);
    }

    // ── CREATE — guardado ─────────────────────────────────────────────────────

    public function test_create_saves_with_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Barrica Mínima')
            ->set('type_id', (string) $this->type->id)
            ->set('material_id', (string) $this->material->id)
            ->set('unit_of_measurement_id', (string) $this->unit->id)
            ->set('capacity', '225')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'user_id'               => $this->viticulturist->id,
            'name'                  => 'Barrica Mínima',
            'type_id'               => $this->type->id,
            'material_id'           => $this->material->id,
            'unit_of_measurement_id' => $this->unit->id,
            'capacity'              => 225,
            'used_capacity'         => 0,
            'archived'              => false,
        ]);
    }

    public function test_create_saves_optional_fields(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Barrica Completa')
            ->set('type_id', (string) $this->type->id)
            ->set('material_id', (string) $this->material->id)
            ->set('unit_of_measurement_id', (string) $this->unit->id)
            ->set('capacity', '225')
            ->set('serial_number', 'BRC-001')
            ->set('supplier_name', 'Tonnelerie Sud')
            ->set('purchase_date', '2023-03-15')
            ->set('oak_type', 'Allier')
            ->set('toast_type', 'medium')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'name'          => 'Barrica Completa',
            'serial_number' => 'BRC-001',
            'supplier_name' => 'Tonnelerie Sud',
            'oak_type'      => 'Allier',
            'toast_type'    => 'medium',
        ]);
    }

    public function test_create_assigns_room(): void
    {
        $room = ContainerRoom::create([
            'user_id' => $this->viticulturist->id,
            'name'    => 'Sala Viti',
        ]);

        Livewire::test(Create::class)
            ->set('name', 'Barrica en Sala')
            ->set('type_id', (string) $this->type->id)
            ->set('material_id', (string) $this->material->id)
            ->set('unit_of_measurement_id', (string) $this->unit->id)
            ->set('capacity', '225')
            ->set('container_room_id', (string) $room->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'name'              => 'Barrica en Sala',
            'container_room_id' => $room->id,
        ]);
    }

    public function test_create_without_room_saves_null(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Barrica Sin Sala')
            ->set('type_id', (string) $this->type->id)
            ->set('material_id', (string) $this->material->id)
            ->set('unit_of_measurement_id', (string) $this->unit->id)
            ->set('capacity', '225')
            ->set('container_room_id', '')
            ->call('save')
            ->assertHasNoErrors();

        $container = Container::where('name', 'Barrica Sin Sala')->first();
        $this->assertNull($container->container_room_id);
    }

    public function test_create_rejects_room_from_other_user(): void
    {
        $other       = $this->makeOtherViticulturist();
        $foreignRoom = ContainerRoom::create(['user_id' => $other->id, 'name' => 'Sala Ajena']);

        Livewire::test(Create::class)
            ->set('name', 'Barrica Intrusión')
            ->set('type_id', (string) $this->type->id)
            ->set('material_id', (string) $this->material->id)
            ->set('unit_of_measurement_id', (string) $this->unit->id)
            ->set('capacity', '225')
            ->set('container_room_id', (string) $foreignRoom->id)
            ->call('save')
            ->assertHasErrors(['container_room_id']);
    }

    // ── EDIT — montaje ────────────────────────────────────────────────────────

    public function test_edit_mounts_data_correctly(): void
    {
        $room = ContainerRoom::create(['user_id' => $this->viticulturist->id, 'name' => 'Sala Mount']);

        $container = $this->makeContainer([
            'name'              => 'Barrica Original',
            'serial_number'     => 'BRC-099',
            'supplier_name'     => 'Tonnelerie Nord',
            'container_room_id' => $room->id,
        ]);
        $container->purchase_date = '2022-05-10';
        $container->save();

        Livewire::test(Edit::class, ['id' => $container->id])
            ->assertSet('name', 'Barrica Original')
            ->assertSet('type_id', $this->type->id)
            ->assertSet('material_id', $this->material->id)
            ->assertSet('capacity', $container->capacity)
            ->assertSet('serial_number', 'BRC-099')
            ->assertSet('supplier_name', 'Tonnelerie Nord')
            ->assertSet('container_room_id', $room->id)
            ->assertSet('purchase_date', '2022-05-10');
    }

    // ── EDIT — validaciones ───────────────────────────────────────────────────

    public function test_edit_validates_name_is_required(): void
    {
        $container = $this->makeContainer();

        Livewire::test(Edit::class, ['id' => $container->id])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_edit_validates_capacity_minimum(): void
    {
        $container = $this->makeContainer();

        Livewire::test(Edit::class, ['id' => $container->id])
            ->set('capacity', '0')
            ->call('save')
            ->assertHasErrors(['capacity']);
    }

    public function test_edit_rejects_capacity_below_used_capacity(): void
    {
        $container = $this->makeContainer(['capacity' => 500, 'used_capacity' => 300]);

        Livewire::test(Edit::class, ['id' => $container->id])
            ->set('capacity', '100')
            ->call('save')
            ->assertHasErrors(['capacity']);
    }

    public function test_edit_rejects_room_from_other_user(): void
    {
        $other       = $this->makeOtherViticulturist();
        $foreignRoom = ContainerRoom::create(['user_id' => $other->id, 'name' => 'Sala Ajena Edit']);
        $container   = $this->makeContainer();

        Livewire::test(Edit::class, ['id' => $container->id])
            ->set('container_room_id', (string) $foreignRoom->id)
            ->call('save')
            ->assertHasErrors(['container_room_id']);
    }

    // ── EDIT — guardado ───────────────────────────────────────────────────────

    public function test_edit_saves_name_change(): void
    {
        $container = $this->makeContainer(['name' => 'Barrica Vieja']);

        Livewire::test(Edit::class, ['id' => $container->id])
            ->set('name', 'Barrica Renovada')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'id'   => $container->id,
            'name' => 'Barrica Renovada',
        ]);
    }

    public function test_edit_assigns_room(): void
    {
        $room      = ContainerRoom::create(['user_id' => $this->viticulturist->id, 'name' => 'Sala Asignada']);
        $container = $this->makeContainer(['container_room_id' => null]);

        Livewire::test(Edit::class, ['id' => $container->id])
            ->set('container_room_id', (string) $room->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'id'                => $container->id,
            'container_room_id' => $room->id,
        ]);
    }

    public function test_edit_removes_room(): void
    {
        $room      = ContainerRoom::create(['user_id' => $this->viticulturist->id, 'name' => 'Sala a Quitar']);
        $container = $this->makeContainer(['container_room_id' => $room->id]);

        Livewire::test(Edit::class, ['id' => $container->id])
            ->set('container_room_id', '')
            ->call('save')
            ->assertHasNoErrors();

        $container->refresh();
        $this->assertNull($container->container_room_id);
    }

    // ── AUTORIZACIÓN ──────────────────────────────────────────────────────────

    public function test_other_viticulturist_cannot_edit_foreign_container_via_livewire(): void
    {
        $other     = $this->makeOtherViticulturist();
        $container = $this->makeContainer();  // pertenece a $this->viticulturist

        // Edit usa Container::where('user_id', Auth::id())->findOrFail($id)
        // → lanza ModelNotFoundException cuando el contenedor es ajeno
        $this->actingAs($other);
        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Edit::class, ['id' => $container->id]);
    }
}
