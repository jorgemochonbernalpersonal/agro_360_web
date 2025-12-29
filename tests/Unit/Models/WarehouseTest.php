<?php

namespace Tests\Unit\Models;

use App\Models\Warehouse;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $warehouse = Warehouse::create([
            'user_id' => $user->id,
            'name' => 'Almacén Principal',
            'location' => 'Edificio A',
            'active' => true,
        ]);

        $this->assertEquals($user->id, $warehouse->user->id);
        $this->assertInstanceOf(User::class, $warehouse->user);
    }

    public function test_warehouse_has_many_stocks(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $warehouse = Warehouse::create([
            'user_id' => $user->id,
            'name' => 'Almacén Principal',
            'active' => true,
        ]);

        $product = \App\Models\PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'registration_number' => 'ES-12345678',
            'withdrawal_period_days' => 14,
        ]);

        $stock1 = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10.5,
            'unit' => 'L',
        ]);

        $stock2 = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5.0,
            'unit' => 'L',
        ]);

        $this->assertCount(2, $warehouse->stocks);
        $warehouse->stocks->each(function ($stock) use ($warehouse) {
            $this->assertEquals($warehouse->id, $stock->warehouse_id);
        });
    }

    public function test_active_is_cast_to_boolean(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $warehouse = Warehouse::create([
            'user_id' => $user->id,
            'name' => 'Almacén Test',
            'active' => true,
        ]);

        $this->assertIsBool($warehouse->active);
        $this->assertTrue($warehouse->active);
    }

    public function test_warehouse_can_be_inactive(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $warehouse = Warehouse::create([
            'user_id' => $user->id,
            'name' => 'Almacén Inactivo',
            'active' => false,
        ]);

        $this->assertFalse($warehouse->active);
    }
}

