<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_check_active_subscription(): void
    {
        $user = User::factory()->create();
        
        Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(20),
        ]);

        $this->assertTrue($user->hasActiveSubscription());
    }

    public function test_user_without_subscription_has_no_active_subscription(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasActiveSubscription());
    }

    public function test_expired_subscription_is_not_active(): void
    {
        $user = User::factory()->create();
        
        Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => 'expired',
            'starts_at' => now()->subDays(60),
            'ends_at' => now()->subDays(30),
        ]);

        $this->assertFalse($user->hasActiveSubscription());
    }

    public function test_beta_user_has_active_access(): void
    {
        $user = User::factory()->create([
            'is_beta_user' => true,
            'beta_ends_at' => now()->addDays(15),
        ]);

        $this->assertTrue($user->hasActiveAccess());
    }

    public function test_expired_beta_user_has_no_active_access(): void
    {
        $user = User::factory()->create([
            'is_beta_user' => true,
            'beta_ends_at' => now()->subDays(5),
        ]);

        $this->assertFalse($user->hasActiveAccess());
    }
}
