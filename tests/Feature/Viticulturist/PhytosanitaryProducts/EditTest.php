<?php

namespace Tests\Feature\Viticulturist\PhytosanitaryProducts;

use App\Livewire\Viticulturist\PhytosanitaryProducts\Edit;
use App\Models\PhytosanitaryProduct;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditTest extends ViticulturistTestCase
{
    public function test_mount_fills_fields(): void
    {
        $v = $this->makeViticulturist();
        $product = $this->makeProduct($v);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['product' => $product])
            ->assertSet('name', 'Producto Original')
            ->assertSet('registration_number', 'ES-11111111')
            ->assertSet('registration_status', 'active')
            ->assertSet('withdrawal_period_days', 5);
    }

    public function test_can_update_product(): void
    {
        $v = $this->makeViticulturist();
        $product = $this->makeProduct($v);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['product' => $product])
            ->set('name', 'Producto Actualizado')
            ->set('manufacturer', 'Empresa SA')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('phytosanitary_products', [
            'id' => $product->id,
            'name' => 'Producto Actualizado',
            'manufacturer' => 'Empresa SA',
        ]);
    }

    public function test_validates_required_fields_on_update(): void
    {
        $v = $this->makeViticulturist();
        $product = $this->makeProduct($v);
        $this->actingAs($v);

        Livewire::test(Edit::class, ['product' => $product])
            ->set('name', '')
            ->set('registration_number', '')
            ->call('save')
            ->assertHasErrors(['name', 'registration_number']);
    }

    public function test_cannot_edit_other_users_product(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeOtherViticulturist();
        $product = $this->makeProduct($other);

        $this->actingAs($v)
            ->get(route('viticulturist.phytosanitary-products.edit', $product))
            ->assertStatus(403);
    }

    private function makeProduct($viticulturist): PhytosanitaryProduct
    {
        return PhytosanitaryProduct::create([
            'user_id' => $viticulturist->id,
            'name' => 'Producto Original',
            'registration_number' => 'ES-11111111',
            'registration_status' => 'active',
            'withdrawal_period_days' => 5,
            'active' => true,
        ]);
    }
}
