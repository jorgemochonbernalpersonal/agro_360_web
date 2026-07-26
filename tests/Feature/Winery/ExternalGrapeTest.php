<?php

namespace Tests\Feature\Winery;

use App\Livewire\Winery\ExternalGrape\Create;
use App\Livewire\Winery\ExternalGrape\Edit;
use App\Models\ExternalGrape;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\WineryTestCase;

class ExternalGrapeTest extends WineryTestCase
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
        $this->get(route('winery.external-grape.index'))
            ->assertOk();
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(Create::class)
            ->set('supplier_name', '')
            ->set('grape_type', '')
            ->set('total_weight_kg', '')
            ->set('entry_date', '')
            ->call('save')
            ->assertHasErrors(['supplier_name', 'grape_type', 'total_weight_kg', 'entry_date']);
    }

    public function test_create_saves_external_grape(): void
    {
        Livewire::test(Create::class)
            ->set('supplier_name', 'Proveedor SA')
            ->set('grape_type', 'grapes')
            ->set('total_weight_kg', '500')
            ->set('entry_date', today()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('external_grapes', [
            'user_id' => $this->winery->id,
            'supplier_name' => 'Proveedor SA',
        ]);
    }

    public function test_edit_saves_changes(): void
    {
        $grape = ExternalGrape::create([
            'user_id' => $this->winery->id,
            'supplier_name' => 'Test Proveedor',
            'grape_type' => 'grapes',
            'total_weight_kg' => 500,
            'used_weight_kg' => 0,
            'entry_date' => today(),
            'status' => 'available',
        ]);

        Livewire::test(Edit::class, ['grape' => $grape])
            ->set('supplier_name', 'Nuevo Proveedor')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('external_grapes', [
            'id' => $grape->id,
            'supplier_name' => 'Nuevo Proveedor',
        ]);
    }

    public function test_other_winery_cannot_edit(): void
    {
        $otherWinery = $this->makeOtherWinery();

        $grape = ExternalGrape::create([
            'user_id' => $this->winery->id,
            'supplier_name' => 'Test Proveedor',
            'grape_type' => 'grapes',
            'total_weight_kg' => 500,
            'used_weight_kg' => 0,
            'entry_date' => today(),
            'status' => 'available',
        ]);

        $this->actingAs($otherWinery)
            ->get(route('winery.external-grape.edit', $grape))
            ->assertForbidden();
    }
}
