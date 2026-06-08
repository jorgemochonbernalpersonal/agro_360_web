<?php

namespace Tests\Feature\Viticulturist\Inventory;

use App\Livewire\Viticulturist\Inventory\ConsumeStock;
use App\Models\PhytosanitaryProduct;
use App\Models\ProductStock;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class ConsumeStockTest extends ViticulturistTestCase
{
    private function makeStock($viticulturist, float $quantity = 10): ProductStock
    {
        $product = PhytosanitaryProduct::create([
            'user_id' => $viticulturist->id,
            'name' => 'Producto',
            'registration_number' => 'ES-12345678',
            'registration_status' => 'active',
            'withdrawal_period_days' => 0,
            'active' => true,
        ]);

        return ProductStock::create([
            'user_id' => $viticulturist->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit' => 'L',
        ]);
    }

    public function test_can_consume_stock(): void
    {
        $v = $this->makeViticulturist();
        $stock = $this->makeStock($v, 10);
        $this->actingAs($v);

        Livewire::test(ConsumeStock::class, ['stock' => $stock])
            ->set('quantity', 3)
            ->set('reason', 'loss')
            ->call('consume')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_stocks', [
            'id' => $stock->id,
            'quantity' => 7,
        ]);
    }

    public function test_validates_quantity_required(): void
    {
        $v = $this->makeViticulturist();
        $stock = $this->makeStock($v);
        $this->actingAs($v);

        Livewire::test(ConsumeStock::class, ['stock' => $stock])
            ->set('quantity', 0)
            ->set('reason', 'loss')
            ->call('consume')
            ->assertHasErrors(['quantity']);
    }

    public function test_cannot_consume_more_than_available(): void
    {
        $v = $this->makeViticulturist();
        $stock = $this->makeStock($v, 5);
        $this->actingAs($v);

        Livewire::test(ConsumeStock::class, ['stock' => $stock])
            ->set('quantity', 20)
            ->set('reason', 'loss')
            ->call('consume');

        $this->assertDatabaseHas('product_stocks', [
            'id' => $stock->id,
            'quantity' => 5,
        ]);
    }

    public function test_other_reason_requires_notes(): void
    {
        $v = $this->makeViticulturist();
        $stock = $this->makeStock($v);
        $this->actingAs($v);

        Livewire::test(ConsumeStock::class, ['stock' => $stock])
            ->set('quantity', 2)
            ->set('reason', 'other')
            ->set('notes', '')
            ->call('consume')
            ->assertHasErrors(['notes']);
    }

    public function test_consume_creates_movement(): void
    {
        $v = $this->makeViticulturist();
        $stock = $this->makeStock($v, 10);
        $this->actingAs($v);

        Livewire::test(ConsumeStock::class, ['stock' => $stock])
            ->set('quantity', 4)
            ->set('reason', 'loss')
            ->call('consume')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_stock_movements', [
            'stock_id' => $stock->id,
            'movement_type' => 'consumption',
        ]);
    }

    public function test_cannot_consume_other_users_stock(): void
    {
        $v = $this->makeViticulturist();
        $other = $this->makeViticulturist();
        $stock = $this->makeStock($other);

        $this->actingAs($v)
            ->get(route('viticulturist.warehouse.stock.consume', $stock))
            ->assertStatus(403);
    }
}
