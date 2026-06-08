<?php

namespace Tests\Feature\Viticulturist\Inventory;

use App\Livewire\Viticulturist\Inventory\EditStock;
use App\Models\PhytosanitaryProduct;
use App\Models\ProductStock;
use App\Models\Unit;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class EditStockTest extends ViticulturistTestCase
{
    private function makeUnit(): Unit
    {
        return Unit::firstOrCreate(
            ['symbol' => 'L'],
            ['name' => 'Litros', 'category' => 'volume', 'active' => true]
        );
    }

    private function makeStock($viticulturist): ProductStock
    {
        $product = PhytosanitaryProduct::create([
            'user_id' => $viticulturist->id,
            'name' => 'Fungicida',
            'registration_number' => 'ES-12345678',
            'registration_status' => 'active',
            'withdrawal_period_days' => 0,
            'active' => true,
        ]);

        return ProductStock::create([
            'user_id' => $viticulturist->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'L',
        ]);
    }

    public function test_mount_fills_fields(): void
    {
        $v = $this->makeViticulturist();
        $this->makeUnit();
        $stock = $this->makeStock($v);
        $this->actingAs($v);

        Livewire::test(EditStock::class, ['stock' => $stock])
            ->assertSet('quantity', 10)
            ->assertSet('unit', 'L');
    }

    public function test_can_update_stock(): void
    {
        $v = $this->makeViticulturist();
        $this->makeUnit();
        $stock = $this->makeStock($v);
        $this->actingAs($v);

        Livewire::test(EditStock::class, ['stock' => $stock])
            ->set('quantity', 20)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_stocks', [
            'id' => $stock->id,
            'quantity' => 20,
        ]);
    }

    public function test_quantity_change_creates_movement(): void
    {
        $v = $this->makeViticulturist();
        $this->makeUnit();
        $stock = $this->makeStock($v);
        $this->actingAs($v);

        Livewire::test(EditStock::class, ['stock' => $stock])
            ->set('quantity', 15)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_stock_movements', [
            'stock_id' => $stock->id,
            'movement_type' => 'adjustment_in',
        ]);
    }

    public function test_cannot_edit_other_users_stock(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $this->makeUnit();
        $stock = $this->makeStock($other);

        $this->actingAs($v)
            ->get(route('viticulturist.warehouse.stock.edit', $stock))
            ->assertStatus(403);
    }
}
