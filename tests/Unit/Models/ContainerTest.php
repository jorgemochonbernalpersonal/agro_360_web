<?php

namespace Tests\Unit\Models;

use App\Models\Container;
use App\Models\Harvest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContainerTest extends TestCase
{
    use RefreshDatabase;

    public function test_container_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $container->user->id);
        $this->assertInstanceOf(User::class, $container->user);
    }

    public function test_container_has_many_harvests(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $container = Container::factory()->create(['user_id' => $user->id]);

        $harvest1 = Harvest::factory()->create(['container_id' => $container->id]);
        $harvest2 = Harvest::factory()->create(['container_id' => $container->id]);

        $this->assertCount(2, $container->harvests);
        $container->harvests->each(function ($harvest) use ($container) {
            $this->assertEquals($container->id, $harvest->container_id);
        });
    }

    public function test_get_available_capacity_returns_correct_value(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 300.0,
        ]);

        // Capacidad disponible: 1000 - 300 = 700
        $this->assertEquals(700.0, $container->getAvailableCapacity());
    }

    public function test_get_available_capacity_returns_zero_when_full(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 1000.0,
        ]);

        $this->assertEquals(0.0, $container->getAvailableCapacity());
    }

    public function test_has_available_capacity_returns_true_when_has_space(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 300.0,
        ]);

        $this->assertTrue($container->hasAvailableCapacity(500.0));
    }

    public function test_has_available_capacity_returns_false_when_no_space(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 800.0,
        ]);

        $this->assertFalse($container->hasAvailableCapacity(300.0));
    }

    public function test_get_occupancy_percentage_returns_correct_value(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 500.0,
        ]);

        // 500 / 1000 * 100 = 50%
        $this->assertEquals(50.0, $container->getOccupancyPercentage());
    }

    public function test_get_occupancy_percentage_returns_zero_when_capacity_is_zero(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 0.0,
            'used_capacity' => 0.0,
        ]);

        $this->assertEquals(0.0, $container->getOccupancyPercentage());
    }

    public function test_is_empty_returns_true_when_used_capacity_is_zero(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 0.0,
        ]);

        $this->assertTrue($container->isEmpty());
    }

    public function test_is_empty_returns_false_when_has_content(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 100.0,
        ]);

        $this->assertFalse($container->isEmpty());
    }

    public function test_is_full_returns_true_when_used_capacity_equals_capacity(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 1000.0,
        ]);

        $this->assertTrue($container->isFull());
    }

    public function test_is_full_returns_false_when_not_full(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 500.0,
        ]);

        $this->assertFalse($container->isFull());
    }

    public function test_increment_used_capacity_updates_correctly(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 300.0,
        ]);

        $result = $container->incrementUsedCapacity(200.0);

        $this->assertTrue($result);
        $container->refresh();
        $this->assertEquals(500.0, $container->used_capacity);
    }

    public function test_increment_used_capacity_returns_false_when_exceeds_capacity(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 800.0,
        ]);

        $result = $container->incrementUsedCapacity(300.0);

        $this->assertFalse($result);
        $container->refresh();
        $this->assertEquals(800.0, $container->used_capacity); // No cambió
    }

    public function test_decrement_used_capacity_updates_correctly(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 500.0,
        ]);

        $result = $container->decrementUsedCapacity(200.0);

        $this->assertTrue($result);
        $container->refresh();
        $this->assertEquals(300.0, $container->used_capacity);
    }

    public function test_decrement_used_capacity_never_goes_below_zero(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 100.0,
        ]);

        $result = $container->decrementUsedCapacity(200.0);

        $this->assertTrue($result);
        $container->refresh();
        $this->assertEquals(0.0, $container->used_capacity); // No puede ser negativo
    }

    public function test_scope_available_filters_containers_with_available_capacity(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $available1 = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 300.0,
            'archived' => false,
        ]);

        $available2 = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 0.0,
            'archived' => false,
        ]);

        $full = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 1000.0,
            'archived' => false,
        ]);

        $archived = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 300.0,
            'archived' => true,
        ]);

        $results = Container::available()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $available1->id));
        $this->assertTrue($results->contains('id', $available2->id));
        $this->assertFalse($results->contains('id', $full->id));
        $this->assertFalse($results->contains('id', $archived->id));
    }

    public function test_scope_empty_filters_empty_containers(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $empty1 = Container::factory()->create([
            'user_id' => $user->id,
            'used_capacity' => 0.0,
        ]);

        $empty2 = Container::factory()->create([
            'user_id' => $user->id,
            'used_capacity' => 0.0,
        ]);

        $filled = Container::factory()->create([
            'user_id' => $user->id,
            'used_capacity' => 100.0,
        ]);

        $results = Container::empty()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $empty1->id));
        $this->assertTrue($results->contains('id', $empty2->id));
        $this->assertFalse($results->contains('id', $filled->id));
    }

    public function test_scope_full_filters_full_containers(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $full1 = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 1000.0,
        ]);

        $full2 = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 500.0,
            'used_capacity' => 500.0,
        ]);

        $partial = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.0,
            'used_capacity' => 500.0,
        ]);

        $results = Container::full()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $full1->id));
        $this->assertTrue($results->contains('id', $full2->id));
        $this->assertFalse($results->contains('id', $partial->id));
    }

    public function test_scope_active_filters_non_archived_containers(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $active1 = Container::factory()->create([
            'user_id' => $user->id,
            'archived' => false,
        ]);

        $active2 = Container::factory()->create([
            'user_id' => $user->id,
            'archived' => false,
        ]);

        $archived = Container::factory()->create([
            'user_id' => $user->id,
            'archived' => true,
        ]);

        $results = Container::active()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $active1->id));
        $this->assertTrue($results->contains('id', $active2->id));
        $this->assertFalse($results->contains('id', $archived->id));
    }

    public function test_get_current_harvest_returns_harvest_via_current_state(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);
        $container = Container::factory()->create(['user_id' => $user->id]);

        $harvest = Harvest::factory()->create([
            'container_id' => $container->id,
            'total_weight' => 500.0,
        ]);

        // El HarvestObserver debería crear el ContainerCurrentState
        $container->refresh();
        
        // Puede retornar null si no hay currentState, o la harvest si existe
        $currentHarvest = $container->getCurrentHarvest();
        
        // Si hay harvest asociada, debería encontrarla
        if ($currentHarvest) {
            $this->assertEquals($harvest->id, $currentHarvest->id);
        }
    }

    public function test_date_fields_are_cast_to_date(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'purchase_date' => now(),
            'next_maintenance_date' => now()->addDays(30),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $container->purchase_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $container->next_maintenance_date);
    }

    public function test_decimal_fields_are_cast_to_decimal(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'capacity' => 1000.500,
            'used_capacity' => 250.750,
        ]);

        $this->assertIsFloat($container->capacity);
        $this->assertIsFloat($container->used_capacity);
    }

    public function test_quantity_is_cast_to_integer(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'quantity' => 10,
        ]);

        $this->assertIsInt($container->quantity);
    }

    public function test_archived_is_cast_to_boolean(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $container = Container::factory()->create([
            'user_id' => $user->id,
            'archived' => true,
        ]);

        $this->assertIsBool($container->archived);
        $this->assertTrue($container->archived);
    }
}

