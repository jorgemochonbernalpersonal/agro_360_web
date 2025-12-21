<?php

namespace Tests\Unit\Models;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_belongs_to_user(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 12.00,
            'currency' => 'EUR',
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->assertEquals($user->id, $payment->user->id);
    }

    public function test_payment_belongs_to_subscription(): void
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

        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => Subscription::PRICE_MONTHLY,
            'currency' => 'EUR',
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->assertEquals($subscription->id, $payment->subscription->id);
    }

    public function test_payment_can_have_null_subscription(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => null,
            'amount' => 12.00,
            'currency' => 'EUR',
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->assertNull($payment->subscription_id);
        $this->assertNull($payment->subscription);
    }

    public function test_status_constants_are_defined(): void
    {
        $this->assertEquals('pending', Payment::STATUS_PENDING);
        $this->assertEquals('completed', Payment::STATUS_COMPLETED);
        $this->assertEquals('failed', Payment::STATUS_FAILED);
        $this->assertEquals('refunded', Payment::STATUS_REFUNDED);
    }

    public function test_amount_is_cast_to_decimal(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 12.50,
            'currency' => 'EUR',
            'status' => Payment::STATUS_PENDING,
        ]);

        // Laravel's decimal cast returns a string
        $this->assertIsNumeric($payment->amount);
        $this->assertEquals('12.50', $payment->amount);
    }

    public function test_paid_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 12.00,
            'currency' => 'EUR',
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $payment->paid_at);
    }

    public function test_paypal_response_is_cast_to_array(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $response = [
            'id' => 'PAY123',
            'status' => 'COMPLETED',
            'amount' => 12.00,
        ];

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 12.00,
            'currency' => 'EUR',
            'status' => Payment::STATUS_COMPLETED,
            'paypal_response' => $response,
        ]);

        $this->assertIsArray($payment->paypal_response);
        $this->assertEquals('PAY123', $payment->paypal_response['id']);
    }

    public function test_mark_as_completed_sets_status_and_paid_at(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 12.00,
            'currency' => 'EUR',
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->assertNull($payment->paid_at);

        $payment->markAsCompleted();

        $payment->refresh();
        $this->assertEquals(Payment::STATUS_COMPLETED, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertInstanceOf(Carbon::class, $payment->paid_at);
    }

    public function test_mark_as_failed_sets_status(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 12.00,
            'currency' => 'EUR',
            'status' => Payment::STATUS_PENDING,
        ]);

        $payment->markAsFailed();

        $payment->refresh();
        $this->assertEquals(Payment::STATUS_FAILED, $payment->status);
    }

    public function test_payment_can_store_paypal_ids(): void
    {
        $user = User::factory()->create(['role' => 'viticulturist']);

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 12.00,
            'currency' => 'EUR',
            'status' => Payment::STATUS_PENDING,
            'paypal_payment_id' => 'PAY-123456789',
            'paypal_order_id' => 'ORDER-987654321',
        ]);

        $this->assertEquals('PAY-123456789', $payment->paypal_payment_id);
        $this->assertEquals('ORDER-987654321', $payment->paypal_order_id);
    }
}

