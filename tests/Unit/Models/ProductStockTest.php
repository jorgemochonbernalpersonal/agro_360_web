<?php

namespace Tests\Unit\Models;

use App\Models\ProductStock;
use App\Models\ProductStockMovement;
use App\Models\PhytosanitaryProduct;
use App\Models\PhytosanitaryTreatment;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\AgriculturalActivity;
use App\Models\Plot;
use App\Models\Campaign;
use Database\Seeders\AutonomousCommunitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\MunicipalitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            AutonomousCommunitySeeder::class,
            ProvinceSeeder::class,
            MunicipalitySeeder::class,
        ]);
    }

    public function test_product_stock_belongs_to_product(): void
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
            'unit' => 'L',
        ]);

        $this->assertEquals($product->id, $stock->product->id);
        $this->assertInstanceOf(PhytosanitaryProduct::class, $stock->product);
    }

    public function test_product_stock_belongs_to_user(): void
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

        $this->assertEquals($user->id, $stock->user->id);
    }

    public function test_product_stock_belongs_to_warehouse(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $warehouse = Warehouse::create([
            'user_id' => $user->id,
            'name' => 'Almacén Principal',
        ]);
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'registration_number' => 'ES-12345678',
            'withdrawal_period_days' => 14,
        ]);

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10.5,
        ]);

        $this->assertEquals($warehouse->id, $stock->warehouse->id);
    }

    public function test_get_available_quantity_returns_quantity_when_not_expired(): void
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
            'expiry_date' => now()->addDays(30),
        ]);

        $this->assertEquals(10.5, $stock->getAvailableQuantity());
    }

    public function test_get_available_quantity_returns_zero_when_expired(): void
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
            'expiry_date' => now()->subDays(1),
        ]);

        $this->assertEquals(0, $stock->getAvailableQuantity());
    }

    public function test_is_expiring_soon_returns_true_when_expires_in_30_days(): void
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
            'expiry_date' => now()->addDays(25),
        ]);

        $this->assertTrue($stock->isExpiringSoon());
    }

    public function test_is_expiring_soon_returns_false_when_not_expiring_soon(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'registration_number' => 'ES-12345678',
            'withdrawal_period_days' => 14,
        ]);

        // Crear stock con fecha de caducidad a más de 30 días
        $stock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 10.5,
            'expiry_date' => now()->addDays(45), // Más de 30 días
        ]);

        $this->assertFalse($stock->isExpiringSoon());
    }

    public function test_is_expired_returns_true_when_expired(): void
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
            'expiry_date' => now()->subDays(1),
        ]);

        $this->assertTrue($stock->isExpired());
    }

    public function test_consume_creates_movement_and_decreases_quantity(): void
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

        $this->actingAs($user);
        $movement = $stock->consume(3.5);

        $stock->refresh();
        $this->assertEquals(7.0, $stock->quantity);
        $this->assertInstanceOf(ProductStockMovement::class, $movement);
        $this->assertEquals('consumption', $movement->movement_type);
        $this->assertEquals(-3.5, $movement->quantity_change);
    }

    public function test_add_stock_creates_movement_and_increases_quantity(): void
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

        $this->actingAs($user);
        $movement = $stock->addStock(5.0, ['unit_price' => 25.50]);

        $stock->refresh();
        $this->assertEquals(15.5, $stock->quantity);
        $this->assertInstanceOf(ProductStockMovement::class, $movement);
        $this->assertEquals('purchase', $movement->movement_type);
        $this->assertEquals(5.0, $movement->quantity_change);
    }

    public function test_scope_available_for_product_filters_correctly(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $product = PhytosanitaryProduct::create([
            'name' => 'Producto Test',
            'registration_number' => 'ES-12345678',
            'withdrawal_period_days' => 14,
        ]);

        $availableStock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 10.5,
            'expiry_date' => now()->addDays(30),
            'active' => true,
        ]);

        $expiredStock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 5.0,
            'expiry_date' => now()->subDays(1),
            'active' => true,
        ]);

        $zeroStock = ProductStock::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 0,
            'active' => true,
        ]);

        $results = ProductStock::availableForProduct($product->id, $user->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $availableStock->id));
        $this->assertFalse($results->contains('id', $expiredStock->id));
        $this->assertFalse($results->contains('id', $zeroStock->id));
    }
}

