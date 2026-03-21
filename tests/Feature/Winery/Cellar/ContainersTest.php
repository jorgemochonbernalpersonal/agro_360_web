<?php

namespace Tests\Feature\Winery\Cellar;

use App\Livewire\Winery\Cellar\Containers\Create;
use App\Livewire\Winery\Cellar\Containers\Edit;
use App\Models\Container;
use App\Models\ContainerRoom;
use App\Models\ContainerType;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class ContainersTest extends WineryTestCase
{
    private User $winery;
    private ContainerType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
        $this->type = ContainerType::firstOrCreate(['name' => 'Depósito Inox']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeContainer(array $attrs = []): Container
    {
        return Container::create(array_merge([
            'user_id'       => $this->winery->id,
            'name'          => 'Cuba Base',
            'type_id'       => $this->type->id,
            'capacity'      => 1000,
            'unit'          => 'kg',
            'used_capacity' => 0,
            'archived'      => false,
        ], $attrs));
    }

    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function test_index_renders(): void
    {
        $this->get(route('winery.containers.index'))
            ->assertOk();
    }

    // ── CREATE — validaciones ─────────────────────────────────────────────────

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('unit', '')
            ->call('save')
            ->assertHasErrors(['name', 'type_id', 'capacity', 'unit']);
    }

    public function test_create_validates_name_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_create_validates_type_id_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('capacity', '500')
            ->call('save')
            ->assertHasErrors(['type_id']);
    }

    public function test_create_validates_capacity_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->call('save')
            ->assertHasErrors(['capacity']);
    }

    public function test_create_validates_type_id_must_exist(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', '99999')
            ->set('capacity', '500')
            ->call('save')
            ->assertHasErrors(['type_id']);
    }

    public function test_create_validates_capacity_must_be_numeric(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', 'abc')
            ->call('save')
            ->assertHasErrors(['capacity']);
    }

    public function test_create_validates_capacity_minimum_one(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '0')
            ->call('save')
            ->assertHasErrors(['capacity']);
    }

    public function test_create_validates_name_max_length(): void
    {
        Livewire::test(Create::class)
            ->set('name', str_repeat('a', 101))
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_create_validates_serial_number_max_length(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->set('serial_number', str_repeat('x', 51))
            ->call('save')
            ->assertHasErrors(['serial_number']);
    }

    public function test_create_validates_supplier_name_max_length(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->set('supplier_name', str_repeat('s', 101))
            ->call('save')
            ->assertHasErrors(['supplier_name']);
    }

    public function test_create_validates_purchase_date_format(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->set('purchase_date', 'no-es-fecha')
            ->call('save')
            ->assertHasErrors(['purchase_date']);
    }

    // ── CREATE — guardado exitoso ─────────────────────────────────────────────

    public function test_create_validates_unit_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->set('unit', '')
            ->call('save')
            ->assertHasErrors(['unit']);
    }

    public function test_create_validates_unit_must_be_valid(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->set('unit', 'toneladas')
            ->call('save')
            ->assertHasErrors(['unit']);
    }

    public function test_create_saves_only_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba Mínima')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '1000')
            ->set('unit', 'kg')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'user_id'       => $this->winery->id,
            'name'          => 'Cuba Mínima',
            'type_id'       => $this->type->id,
            'capacity'      => 1000,
            'unit'          => 'kg',
            'used_capacity' => 0,
            'archived'      => false,
        ]);
    }

    public function test_create_saves_with_unit_litros(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Depósito Vino')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '10000')
            ->set('unit', 'litros')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'name' => 'Depósito Vino',
            'unit' => 'litros',
        ]);
    }

    public function test_create_saves_with_all_optional_fields(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba Completa')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '5000')
            ->set('unit', 'kg')
            ->set('serial_number', 'SN-001')
            ->set('description', 'Depósito de acero inoxidable para fermentación')
            ->set('purchase_date', '2023-06-15')
            ->set('supplier_name', 'Inox Bodegas S.L.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'user_id'       => $this->winery->id,
            'name'          => 'Cuba Completa',
            'type_id'       => $this->type->id,
            'capacity'      => 5000,
            'unit'          => 'kg',
            'serial_number' => 'SN-001',
            'supplier_name' => 'Inox Bodegas S.L.',
        ]);
    }

    public function test_create_nullifies_empty_optional_fields(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba Vacíos')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->set('serial_number', '')
            ->set('description', '')
            ->set('supplier_name', '')
            ->call('save')
            ->assertHasNoErrors();

        $container = Container::where('name', 'Cuba Vacíos')->first();
        $this->assertNull($container->serial_number);
        $this->assertNull($container->description);
        $this->assertNull($container->supplier_name);
    }

    public function test_create_assigns_used_capacity_zero(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba Nueva')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '800')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'name'          => 'Cuba Nueva',
            'used_capacity' => 0,
        ]);
    }

    // ── EDIT — montaje ────────────────────────────────────────────────────────

    public function test_edit_mounts_data_correctly(): void
    {
        $type2 = ContainerType::firstOrCreate(['name' => 'Barrica']);

        $container = $this->makeContainer([
            'name'          => 'Barrica Original',
            'type_id'       => $type2->id,
            'capacity'      => 225,
            'unit'          => 'litros',
            'serial_number' => 'BRC-042',
            'description'   => 'Barrica de roble francés',
            'supplier_name' => 'Tonnelerie Rousseau',
        ]);
        $container->purchase_date = '2022-03-10';
        $container->save();

        Livewire::test(Edit::class, ['container' => $container])
            ->assertSet('name', 'Barrica Original')
            ->assertSet('type_id', (string) $type2->id)
            ->assertSet('capacity', '225.00')
            ->assertSet('unit', 'litros')
            ->assertSet('serial_number', 'BRC-042')
            ->assertSet('description', 'Barrica de roble francés')
            ->assertSet('supplier_name', 'Tonnelerie Rousseau')
            ->assertSet('purchase_date', '2022-03-10');
    }

    // ── EDIT — validaciones ───────────────────────────────────────────────────

    public function test_edit_validates_required_fields(): void
    {
        $container = $this->makeContainer();

        Livewire::test(Edit::class, ['container' => $container])
            ->set('name', '')
            ->set('type_id', '')
            ->set('capacity', '')
            ->call('save')
            ->assertHasErrors(['name', 'type_id', 'capacity']);
    }

    public function test_edit_validates_name_is_required(): void
    {
        $container = $this->makeContainer();

        Livewire::test(Edit::class, ['container' => $container])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_edit_validates_name_max_length(): void
    {
        $container = $this->makeContainer();

        Livewire::test(Edit::class, ['container' => $container])
            ->set('name', str_repeat('n', 101))
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_edit_validates_capacity_minimum(): void
    {
        $container = $this->makeContainer();

        Livewire::test(Edit::class, ['container' => $container])
            ->set('capacity', '0')
            ->call('save')
            ->assertHasErrors(['capacity']);
    }

    public function test_edit_rejects_capacity_below_used_capacity(): void
    {
        $container = $this->makeContainer(['capacity' => 1000, 'used_capacity' => 800]);

        Livewire::test(Edit::class, ['container' => $container])
            ->set('capacity', '500')   // 500 < 800 utilizado
            ->call('save')
            ->assertHasErrors(['capacity']);
    }

    public function test_edit_rejects_capacity_below_wine_volume(): void
    {
        $container = $this->makeContainer([
            'capacity'           => 1000,
            'used_capacity'      => 0,
            'wine_volume_liters' => 800,
            'unit'               => 'litros',
        ]);

        Livewire::test(Edit::class, ['container' => $container])
            ->set('capacity', '500')   // 500 < 800 de vino
            ->call('save')
            ->assertHasErrors(['capacity']);
    }

    public function test_edit_accepts_capacity_equal_to_used_capacity(): void
    {
        $container = $this->makeContainer(['capacity' => 1000, 'used_capacity' => 800]);

        Livewire::test(Edit::class, ['container' => $container])
            ->set('capacity', '800')   // igual al utilizado → válido
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_edit_rejects_unit_change_when_container_has_stock(): void
    {
        $container = $this->makeContainer(['capacity' => 1000, 'used_capacity' => 500, 'unit' => 'kg']);

        Livewire::test(Edit::class, ['container' => $container])
            ->set('unit', 'litros')
            ->call('save')
            ->assertHasErrors(['capacity']);  // el error se añade en capacity
    }

    public function test_edit_allows_unit_change_when_container_is_empty(): void
    {
        $container = $this->makeContainer(['capacity' => 1000, 'used_capacity' => 0, 'unit' => 'kg']);

        Livewire::test(Edit::class, ['container' => $container])
            ->set('unit', 'litros')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'id'   => $container->id,
            'unit' => 'litros',
        ]);
    }

    // ── EDIT — guardado exitoso ───────────────────────────────────────────────

    public function test_edit_saves_name_change(): void
    {
        $container = $this->makeContainer(['name' => 'Cuba Vieja']);

        Livewire::test(Edit::class, ['container' => $container])
            ->set('name', 'Cuba Renovada')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'id'   => $container->id,
            'name' => 'Cuba Renovada',
        ]);
    }

    public function test_edit_saves_all_fields(): void
    {
        $type2 = ContainerType::firstOrCreate(['name' => 'Tinaja']);
        $container = $this->makeContainer(['unit' => 'kg']);

        Livewire::test(Edit::class, ['container' => $container])
            ->set('name', 'Tinaja Actualizada')
            ->set('type_id', (string) $type2->id)
            ->set('capacity', '3000')
            ->set('unit', 'litros')
            ->set('serial_number', 'TJ-999')
            ->set('description', 'Tinaja de barro cocido')
            ->set('purchase_date', '2024-01-20')
            ->set('supplier_name', 'Alfarería Manchega')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'id'            => $container->id,
            'name'          => 'Tinaja Actualizada',
            'type_id'       => $type2->id,
            'capacity'      => 3000,
            'unit'          => 'litros',
            'serial_number' => 'TJ-999',
            'supplier_name' => 'Alfarería Manchega',
        ]);
    }

    public function test_edit_clears_optional_fields_to_null(): void
    {
        $container = $this->makeContainer([
            'serial_number' => 'SN-000',
            'supplier_name' => 'Proveedor Original',
        ]);

        Livewire::test(Edit::class, ['container' => $container])
            ->set('serial_number', '')
            ->set('supplier_name', '')
            ->call('save')
            ->assertHasNoErrors();

        $container->refresh();
        $this->assertNull($container->serial_number);
        $this->assertNull($container->supplier_name);
    }

    // ── SALA (container_room_id) ──────────────────────────────────────────────

    public function test_create_assigns_room(): void
    {
        $room = ContainerRoom::create([
            'user_id' => $this->winery->id,
            'name'    => 'Sala Fermentación',
        ]);

        Livewire::test(Create::class)
            ->set('name', 'Cuba en Sala')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->set('container_room_id', (string) $room->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('containers', [
            'name'              => 'Cuba en Sala',
            'container_room_id' => $room->id,
        ]);
    }

    public function test_create_without_room_saves_null(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Cuba Sin Sala')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->set('container_room_id', '')
            ->call('save')
            ->assertHasNoErrors();

        $container = Container::where('name', 'Cuba Sin Sala')->first();
        $this->assertNull($container->container_room_id);
    }

    public function test_create_rejects_room_from_other_user(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $foreignRoom = ContainerRoom::create([
            'user_id' => $otherWinery->id,
            'name'    => 'Sala Ajena',
        ]);

        Livewire::test(Create::class)
            ->set('name', 'Cuba Intrusión')
            ->set('type_id', (string) $this->type->id)
            ->set('capacity', '500')
            ->set('container_room_id', (string) $foreignRoom->id)
            ->call('save')
            ->assertHasErrors(['container_room_id']);
    }

    public function test_edit_mounts_room_correctly(): void
    {
        $room = ContainerRoom::create([
            'user_id' => $this->winery->id,
            'name'    => 'Sala Crianza',
        ]);

        $container = $this->makeContainer(['container_room_id' => $room->id]);

        Livewire::test(Edit::class, ['container' => $container])
            ->assertSet('container_room_id', (string) $room->id);
    }

    public function test_edit_assigns_room(): void
    {
        $room      = ContainerRoom::create(['user_id' => $this->winery->id, 'name' => 'Sala Nueva']);
        $container = $this->makeContainer(['container_room_id' => null]);

        Livewire::test(Edit::class, ['container' => $container])
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
        $room      = ContainerRoom::create(['user_id' => $this->winery->id, 'name' => 'Sala a Quitar']);
        $container = $this->makeContainer(['container_room_id' => $room->id]);

        Livewire::test(Edit::class, ['container' => $container])
            ->set('container_room_id', '')
            ->call('save')
            ->assertHasNoErrors();

        $container->refresh();
        $this->assertNull($container->container_room_id);
    }

    public function test_edit_rejects_room_from_other_user(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $foreignRoom = ContainerRoom::create(['user_id' => $otherWinery->id, 'name' => 'Sala Ajena Edit']);
        $container   = $this->makeContainer();

        Livewire::test(Edit::class, ['container' => $container])
            ->set('container_room_id', (string) $foreignRoom->id)
            ->call('save')
            ->assertHasErrors(['container_room_id']);
    }

    // ── AUTORIZACIÓN ──────────────────────────────────────────────────────────

    public function test_other_winery_cannot_access_edit_page(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $container   = $this->makeContainer();

        $this->actingAs($otherWinery)
            ->get(route('winery.containers.edit', $container))
            ->assertForbidden();
    }

    public function test_other_winery_cannot_edit_via_livewire(): void
    {
        $otherWinery = $this->makeOtherWinery();
        $container   = $this->makeContainer();

        $this->actingAs($otherWinery);

        Livewire::test(Edit::class, ['container' => $container])
            ->assertStatus(403);
    }
}
