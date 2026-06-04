<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Subscriptions\Index as SubscriptionsIndex;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for the Admin Subscriptions management panel.
 *
 * Covers: access control, stat display, filtering, cancellation.
 */
class SubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $winery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin',  'email_verified_at' => now()]);
        $this->winery = User::factory()->create(['role' => 'winery', 'email_verified_at' => now()]);
    }

    // ── access ────────────────────────────────────────────────────────────────

    public function test_admin_can_access_subscriptions_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.subscriptions.index'))
            ->assertOk();
    }

    public function test_non_admin_cannot_access_subscriptions_page(): void
    {
        $this->actingAs($this->winery)
            ->get(route('admin.subscriptions.index'))
            ->assertForbidden();
    }

    // ── stats ─────────────────────────────────────────────────────────────────

    public function test_stats_counts_by_status(): void
    {
        Subscription::factory()->active()->create(['user_id' => $this->winery->id]);
        Subscription::factory()->cancelled()->create(['user_id' => $this->winery->id]);
        Subscription::factory()->expired()->create(['user_id' => $this->winery->id]);

        $this->actingAs($this->admin);

        Livewire::test(SubscriptionsIndex::class)
            ->assertViewHas('stats', fn ($stats) => $stats['total'] === 3 &&
                $stats['active'] === 1 &&
                $stats['cancelled'] === 1 &&
                $stats['expired'] === 1
            );
    }

    // ── filtering ─────────────────────────────────────────────────────────────

    public function test_filter_by_active_status_shows_only_active(): void
    {
        $active = Subscription::factory()->active()->create(['user_id' => $this->winery->id]);
        $cancelled = Subscription::factory()->cancelled()->create(['user_id' => $this->winery->id]);

        $this->actingAs($this->admin);

        Livewire::test(SubscriptionsIndex::class)
            ->set('filterStatus', 'active')
            ->assertViewHas('subscriptions', fn ($subs) => $subs->contains('id', $active->id) &&
                ! $subs->contains('id', $cancelled->id)
            );
    }

    public function test_filter_by_monthly_plan(): void
    {
        $monthly = Subscription::factory()->create(['user_id' => $this->winery->id, 'plan_type' => 'monthly']);
        $yearly = Subscription::factory()->create(['user_id' => $this->winery->id, 'plan_type' => 'yearly']);

        $this->actingAs($this->admin);

        Livewire::test(SubscriptionsIndex::class)
            ->set('filterPlan', 'monthly')
            ->assertViewHas('subscriptions', fn ($subs) => $subs->contains('id', $monthly->id) &&
                ! $subs->contains('id', $yearly->id)
            );
    }

    public function test_search_filters_by_user_name(): void
    {
        $userA = User::factory()->create(['name' => 'Juan García', 'role' => 'viticulturist']);
        $userB = User::factory()->create(['name' => 'Pedro Martínez', 'role' => 'viticulturist']);

        $subA = Subscription::factory()->create(['user_id' => $userA->id]);
        $subB = Subscription::factory()->create(['user_id' => $userB->id]);

        $this->actingAs($this->admin);

        Livewire::test(SubscriptionsIndex::class)
            ->set('search', 'Juan')
            ->assertViewHas('subscriptions', fn ($subs) => $subs->contains('id', $subA->id) &&
                ! $subs->contains('id', $subB->id)
            );
    }

    public function test_search_filters_by_user_email(): void
    {
        $userA = User::factory()->create(['email' => 'test@example.com', 'role' => 'viticulturist']);
        $userB = User::factory()->create(['role' => 'viticulturist']);

        $subA = Subscription::factory()->create(['user_id' => $userA->id]);
        $subB = Subscription::factory()->create(['user_id' => $userB->id]);

        $this->actingAs($this->admin);

        Livewire::test(SubscriptionsIndex::class)
            ->set('search', 'test@example.com')
            ->assertViewHas('subscriptions', fn ($subs) => $subs->contains('id', $subA->id) &&
                ! $subs->contains('id', $subB->id)
            );
    }

    // ── cancellation ──────────────────────────────────────────────────────────

    public function test_admin_can_cancel_active_subscription(): void
    {
        $subscription = Subscription::factory()->active()->create(['user_id' => $this->winery->id]);

        $this->actingAs($this->admin);

        Livewire::test(SubscriptionsIndex::class)
            ->call('cancelSubscription', $subscription->id);

        $this->assertSame('cancelled', $subscription->fresh()->status);
        $this->assertNotNull($subscription->fresh()->cancelled_at);
    }

    public function test_cancelling_already_cancelled_subscription_does_not_error(): void
    {
        $subscription = Subscription::factory()->cancelled()->create(['user_id' => $this->winery->id]);

        $this->actingAs($this->admin);

        // Should not throw — just shows error toast
        Livewire::test(SubscriptionsIndex::class)
            ->call('cancelSubscription', $subscription->id);

        // Status unchanged
        $this->assertSame('cancelled', $subscription->fresh()->status);
    }

    // ── combined filters ──────────────────────────────────────────────────────

    public function test_status_and_plan_filters_combine(): void
    {
        $userA = User::factory()->create(['role' => 'viticulturist']);
        $userB = User::factory()->create(['role' => 'viticulturist']);

        $match = Subscription::factory()->active()->create(['user_id' => $userA->id, 'plan_type' => 'monthly']);
        $noMatch = Subscription::factory()->active()->create(['user_id' => $userB->id, 'plan_type' => 'yearly']);

        $this->actingAs($this->admin);

        Livewire::test(SubscriptionsIndex::class)
            ->set('filterStatus', 'active')
            ->set('filterPlan', 'monthly')
            ->assertViewHas('subscriptions', fn ($subs) => $subs->contains('id', $match->id) &&
                ! $subs->contains('id', $noMatch->id)
            );
    }
}
