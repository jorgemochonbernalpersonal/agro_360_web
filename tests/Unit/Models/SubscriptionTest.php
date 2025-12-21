<?php

namespace Tests\Unit\Models;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertEquals($user->id, $subscription->user->id);
    }

    public function test_subscription_has_many_payments(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $payment1 = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);

        $payment2 = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);

        $this->assertCount(2, $subscription->payments);
        $this->assertTrue($subscription->payments->contains('id', $payment1->id));
        $this->assertTrue($subscription->payments->contains('id', $payment2->id));
    }

    public function test_is_active_returns_true_when_status_is_active_and_not_expired(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(20),
        ]);

        $this->assertTrue($subscription->isActive());
    }

    public function test_is_active_returns_false_when_status_is_not_active(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_CANCELLED,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(20),
        ]);

        $this->assertFalse($subscription->isActive());
    }

    public function test_is_active_returns_false_when_expired(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
        ]);

        $this->assertFalse($subscription->isActive());
    }

    public function test_is_expired_returns_true_when_ends_at_is_past(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subDays(5),
        ]);

        $this->assertTrue($subscription->isExpired());
    }

    public function test_is_expired_returns_false_when_ends_at_is_future(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertFalse($subscription->isExpired());
    }

    public function test_cancel_method_sets_status_and_cancelled_at(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertNull($subscription->cancelled_at);

        $subscription->cancel();

        $subscription->refresh();
        $this->assertEquals(Subscription::STATUS_CANCELLED, $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);
    }

    public function test_plan_type_constants_are_defined(): void
    {
        $this->assertEquals('monthly', Subscription::PLAN_MONTHLY);
        $this->assertEquals('yearly', Subscription::PLAN_YEARLY);
    }

    public function test_status_constants_are_defined(): void
    {
        $this->assertEquals('active', Subscription::STATUS_ACTIVE);
        $this->assertEquals('cancelled', Subscription::STATUS_CANCELLED);
        $this->assertEquals('expired', Subscription::STATUS_EXPIRED);
    }

    public function test_price_constants_are_defined(): void
    {
        $this->assertEquals(12.00, Subscription::PRICE_MONTHLY);
        $this->assertEquals(120.00, Subscription::PRICE_YEARLY);
    }

    public function test_starts_at_and_ends_at_are_cast_to_datetime(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertInstanceOf(Carbon::class, $subscription->starts_at);
        $this->assertInstanceOf(Carbon::class, $subscription->ends_at);
    }

    public function test_cancelled_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_CANCELLED,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'cancelled_at' => now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $subscription->cancelled_at);
    }

    public function test_amount_is_cast_to_decimal(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => Subscription::PLAN_MONTHLY,
            'amount' => Subscription::PRICE_MONTHLY,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Laravel's decimal cast returns a string, not a float
        $this->assertIsNumeric($subscription->amount);
        $this->assertEquals('12.00', $subscription->amount);
    }
}

