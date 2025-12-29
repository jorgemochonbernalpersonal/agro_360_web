<?php

namespace Tests\Unit\Models;

use App\Models\ProductStockMovement;
use App\Models\ProductStock;
use App\Models\PhytosanitaryProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_stock_movement_belongs_to_stock(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'registration_number' => 'ES-12345678',
            'withdrawal_period_days' => 14,
        ]);

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 10.5,
        ]);

        $movement = ProductStockMovement::create([
            'stock_id' => $stock->id,
            'user_id' => $user->id,
            'movement_type' => 'purchase',
            'quantity_change' => 5.0,
            'quantity_before' => 10.5,
            'quantity_after' => 15.5,
        ]);

        $this->assertEquals($stock->id, $movement->stock->id);
        $this->assertInstanceOf(ProductStock::class, $movement->stock);
    }

    public function test_is_inbound_returns_true_for_purchase(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'registration_number' => 'ES-12345678',
            'withdrawal_period_days' => 14,
        ]);

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 10.5,
        ]);

        $movement = ProductStockMovement::create([
            'stock_id' => $stock->id,
            'user_id' => $user->id,
            'movement_type' => 'purchase',
            'quantity_change' => 5.0,
            'quantity_before' => 10.5,
            'quantity_after' => 15.5,
        ]);

        $this->assertTrue($movement->isInbound());
    }

    public function test_is_outbound_returns_true_for_consumption(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'registration_number' => 'ES-12345678',
            'withdrawal_period_days' => 14,
        ]);

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 10.5,
        ]);

        $movement = ProductStockMovement::create([
            'stock_id' => $stock->id,
            'user_id' => $user->id,
            'movement_type' => 'consumption',
            'quantity_change' => -3.5,
            'quantity_before' => 10.5,
            'quantity_after' => 7.0,
        ]);

        $this->assertTrue($movement->isOutbound());
    }

    public function test_get_movement_description_returns_correct_description(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'registration_number' => 'ES-12345678',
            'withdrawal_period_days' => 14,
        ]);

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 10.5,
        ]);

        $purchase = ProductStockMovement::create([
            'stock_id' => $stock->id,
            'user_id' => $user->id,
            'movement_type' => 'purchase',
            'quantity_change' => 5.0,
            'quantity_before' => 10.5,
            'quantity_after' => 15.5,
        ]);

        $consumption = ProductStockMovement::create([
            'stock_id' => $stock->id,
            'user_id' => $user->id,
            'movement_type' => 'consumption',
            'quantity_change' => -3.5,
            'quantity_before' => 15.5,
            'quantity_after' => 12.0,
        ]);

        $this->assertEquals('Compra/Entrada', $purchase->getMovementDescription());
        $this->assertEquals('Consumo por tratamiento', $consumption->getMovementDescription());
    }
}

