<?php

namespace Tests\Unit\Models;

use App\Models\HarvestContainer;
use App\Models\Harvest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HarvestContainerTest extends TestCase
{
    use RefreshDatabase;

    public function test_harvest_container_belongs_to_harvest(): void
    {
        $harvest = Harvest::factory()->create();

        $container = HarvestContainer::factory()->create([
            'harvest_id' => $harvest->id,
        ]);

        $this->assertEquals($harvest->id, $container->harvest->id);
        $this->assertInstanceOf(Harvest::class, $container->harvest);
    }

    public function test_harvest_container_has_many_harvests(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $container = HarvestContainer::factory()->create(['user_id' => $user->id]);

        $harvest1 = Harvest::factory()->create(['container_id' => $container->id]);
        $harvest2 = Harvest::factory()->create(['container_id' => $container->id]);

        $this->assertCount(2, $container->harvests);
        $container->harvests->each(function ($harvest) use ($container) {
            $this->assertEquals($container->id, $harvest->container_id);
        });
    }

    public function test_weight_per_unit_is_calculated_automatically(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'weight' => 1000.0,
            'quantity' => 10,
        ]);

        // Debe calcular: 1000 / 10 = 100.0
        $this->assertEquals(100.0, $container->weight_per_unit);
    }

    public function test_weight_per_unit_is_null_when_quantity_is_zero(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'weight' => 1000.0,
            'quantity' => 0,
        ]);

        // No debe calcular si quantity es 0
        $this->assertNull($container->weight_per_unit);
    }

    public function test_weight_per_unit_is_null_when_weight_is_null(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'weight' => null,
            'quantity' => 10,
        ]);

        $this->assertNull($container->weight_per_unit);
    }

    public function test_scope_of_type_filters_by_container_type(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $crate1 = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'container_type' => 'crate',
        ]);

        $crate2 = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'container_type' => 'crate',
        ]);

        $box = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'container_type' => 'box',
        ]);

        $results = HarvestContainer::ofType('crate')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $crate1->id));
        $this->assertTrue($results->contains('id', $crate2->id));
        $this->assertFalse($results->contains('id', $box->id));
    }

    public function test_scope_with_status_filters_by_status(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $stored1 = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'status' => 'stored',
        ]);

        $stored2 = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'status' => 'stored',
        ]);

        $delivered = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
        ]);

        $results = HarvestContainer::withStatus('stored')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $stored1->id));
        $this->assertTrue($results->contains('id', $stored2->id));
        $this->assertFalse($results->contains('id', $delivered->id));
    }

    public function test_scope_delivered_filters_delivered_containers(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $delivered1 = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
        ]);

        $delivered2 = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
        ]);

        $stored = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'status' => 'stored',
        ]);

        $results = HarvestContainer::delivered()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $delivered1->id));
        $this->assertTrue($results->contains('id', $delivered2->id));
        $this->assertFalse($results->contains('id', $stored->id));
    }

    public function test_scope_stored_filters_stored_containers(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $stored1 = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'status' => 'stored',
        ]);

        $delivered = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
        ]);

        $results = HarvestContainer::stored()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($stored1->id, $results->first()->id);
    }

    public function test_scope_available_filters_containers_without_harvest(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $available1 = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'harvest_id' => null,
        ]);

        $available2 = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'harvest_id' => null,
        ]);

        $assigned = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'harvest_id' => Harvest::factory()->create()->id,
        ]);

        $results = HarvestContainer::available()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $available1->id));
        $this->assertTrue($results->contains('id', $available2->id));
        $this->assertFalse($results->contains('id', $assigned->id));
    }

    public function test_scope_assigned_filters_containers_with_harvest(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $assigned1 = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'harvest_id' => Harvest::factory()->create()->id,
        ]);

        $assigned2 = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'harvest_id' => Harvest::factory()->create()->id,
        ]);

        $available = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'harvest_id' => null,
        ]);

        $results = HarvestContainer::assigned()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $assigned1->id));
        $this->assertTrue($results->contains('id', $assigned2->id));
        $this->assertFalse($results->contains('id', $available->id));
    }

    public function test_is_delivered_returns_true_when_status_is_delivered(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
        ]);

        $this->assertTrue($container->isDelivered());
    }

    public function test_is_delivered_returns_false_when_status_is_not_delivered(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'status' => 'stored',
        ]);

        $this->assertFalse($container->isDelivered());
    }

    public function test_is_empty_returns_true_when_status_is_empty(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'status' => 'empty',
        ]);

        $this->assertTrue($container->isEmpty());
    }

    public function test_is_available_returns_true_when_harvest_id_is_null(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'harvest_id' => null,
        ]);

        $this->assertTrue($container->isAvailable());
    }

    public function test_is_available_returns_false_when_harvest_id_is_not_null(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'harvest_id' => Harvest::factory()->create()->id,
        ]);

        $this->assertFalse($container->isAvailable());
    }

    public function test_is_assigned_returns_true_when_harvest_id_is_not_null(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'harvest_id' => Harvest::factory()->create()->id,
        ]);

        $this->assertTrue($container->isAssigned());
    }

    public function test_is_assigned_returns_false_when_harvest_id_is_null(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'harvest_id' => null,
        ]);

        $this->assertFalse($container->isAssigned());
    }

    public function test_date_fields_are_cast_to_date(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'filled_date' => now(),
            'delivery_date' => now()->addDays(1),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $container->filled_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $container->delivery_date);
    }

    public function test_decimal_fields_are_cast_to_decimal(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'weight' => 1000.500,
            'weight_per_unit' => 100.250,
        ]);

        $this->assertIsFloat($container->weight);
        $this->assertIsFloat($container->weight_per_unit);
    }

    public function test_quantity_is_cast_to_integer(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = HarvestContainer::factory()->create([
            'user_id' => $user->id,
            'quantity' => 10,
        ]);

        $this->assertIsInt($container->quantity);
    }
}

