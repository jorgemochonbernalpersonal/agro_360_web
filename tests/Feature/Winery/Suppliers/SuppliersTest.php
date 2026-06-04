<?php

namespace Tests\Feature\Winery\Suppliers;

use App\Livewire\Winery\Suppliers\Create;
use App\Livewire\Winery\Suppliers\Edit;
use App\Livewire\Winery\Suppliers\Index;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class SuppliersTest extends WineryTestCase
{
    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->winery = $this->makeWinery();
        $this->actingAs($this->winery);
    }

    public function test_winery_can_view_index(): void
    {
        $this->get(route('winery.suppliers.index'))->assertOk();
    }

    public function test_guest_cannot_access(): void
    {
        $this->app['auth']->guard()->logout();

        $this->get(route('winery.suppliers.index'))->assertRedirect();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('name', '')
            ->set('category', '')
            ->call('save')
            ->assertHasErrors(['name', 'category']);
    }

    public function test_winery_can_create_supplier(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Proveedor Test')
            ->set('category', 'other')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('suppliers', [
            'user_id' => $this->winery->id,
            'name' => 'Proveedor Test',
            'category' => 'other',
        ]);
    }

    public function test_winery_can_edit_supplier(): void
    {
        $supplier = Supplier::create([
            'user_id' => $this->winery->id,
            'name' => 'Antiguo Nombre',
            'category' => 'other',
            'active' => true,
        ]);

        Livewire::test(Edit::class, ['supplier' => $supplier])
            ->set('name', 'Nombre Actualizado')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Nombre Actualizado',
        ]);
    }

    public function test_winery_cannot_edit_other_winery_supplier(): void
    {
        $supplier = Supplier::create([
            'user_id' => $this->winery->id,
            'name' => 'Proveedor Protegido',
            'category' => 'other',
            'active' => true,
        ]);

        $otherWinery = $this->makeOtherWinery();

        $this->actingAs($otherWinery)
            ->get(route('winery.suppliers.edit', $supplier))
            ->assertForbidden();
    }

    public function test_winery_can_delete_supplier(): void
    {
        $supplier = Supplier::create([
            'user_id' => $this->winery->id,
            'name' => 'Proveedor a Eliminar',
            'category' => 'other',
            'active' => true,
        ]);

        Livewire::test(Index::class)
            ->call('delete', $supplier->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
