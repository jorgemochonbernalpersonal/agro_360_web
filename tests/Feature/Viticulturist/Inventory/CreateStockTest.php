<?php

namespace Tests\Feature\Viticulturist\Inventory;

use App\Livewire\Viticulturist\Inventory\CreateStock;
use App\Models\PhytosanitaryProduct;
use App\Models\ProductStock;
use App\Models\Unit;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class CreateStockTest extends ViticulturistTestCase
{
    private function makeUnit(): Unit
    {
        return Unit::firstOrCreate(
            ['symbol' => 'L'],
            ['name' => 'Litros', 'category' => 'volume', 'active' => true]
        );
    }

    private function makeProduct($viticulturist): PhytosanitaryProduct
    {
        return PhytosanitaryProduct::create([
            'user_id' => $viticulturist->id,
            'name' => 'Fungicida Test',
            'registration_number' => 'ES-12345678',
            'registration_status' => 'active',
            'withdrawal_period_days' => 0,
            'active' => true,
        ]);
    }

    public function test_can_create_stock(): void
    {
        $v = $this->makeViticulturist();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($v);
        $this->actingAs($v);

        Livewire::test(CreateStock::class)
            ->set('product_id', (string) $product->id)
            ->set('quantity', '10')
            ->set('unit', 'L')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_stocks', [
            'user_id' => $v->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_validates_product_required(): void
    {
        $v = $this->makeViticulturist();
        $this->makeUnit();
        $this->actingAs($v);

        Livewire::test(CreateStock::class)
            ->set('product_id', '')
            ->set('quantity', '10')
            ->set('unit', 'L')
            ->call('save')
            ->assertHasErrors(['product_id']);
    }

    public function test_validates_quantity_required(): void
    {
        $v = $this->makeViticulturist();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($v);
        $this->actingAs($v);

        Livewire::test(CreateStock::class)
            ->set('product_id', (string) $product->id)
            ->set('quantity', '0')
            ->set('unit', 'L')
            ->call('save')
            ->assertHasErrors(['quantity']);
    }

    public function test_adding_stock_creates_movement(): void
    {
        $v = $this->makeViticulturist();
        $this->makeUnit();
        $product = $this->makeProduct($v);
        $this->actingAs($v);

        Livewire::test(CreateStock::class)
            ->set('product_id', (string) $product->id)
            ->set('quantity', '5')
            ->set('unit', 'L')
            ->call('save')
            ->assertHasNoErrors();

        $stock = ProductStock::where('user_id', $v->id)->where('product_id', $product->id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(5, (float) $stock->quantity);
        $this->assertDatabaseHas('product_stock_movements', [
            'stock_id' => $stock->id,
            'movement_type' => 'purchase',
        ]);
    }
}
