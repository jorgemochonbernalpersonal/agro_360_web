<?php

namespace Tests\Feature\Commands;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckExpiredSubscriptionsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_marks_expired_subscriptions(): void
    {
        $user = User::factory()->create();

        // Suscripción expirada
        $expiredSubscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'ends_at' => now()->subDays(1),
        ]);

        // Suscripción activa
        $activeSubscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'ends_at' => now()->addDays(30),
        ]);

        $this->artisan('subscriptions:check-expired')
            ->assertSuccessful();

        // Verificar que la expirada cambió de estado
        $this->assertDatabaseHas('subscriptions', [
            'id' => $expiredSubscription->id,
            'status' => 'expired',
        ]);

        // Verificar que la activa permanece activa
        $this->assertDatabaseHas('subscriptions', [
            'id' => $activeSubscription->id,
            'status' => 'active',
        ]);
    }

    public function test_command_sends_expiration_notifications(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // Suscripción que expira en 3 días
        $soonToExpire = Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'ends_at' => now()->addDays(3)->startOfDay(),
        ]);

        $this->artisan('subscriptions:check-expired', ['--notify-days' => '7,3,1'])
            ->assertSuccessful();

        // Verificar que se envió notificación
        Notification::assertSentTo(
            [$user],
            \App\Notifications\SubscriptionExpiringSoon::class
        );
    }

    public function test_command_handles_multiple_expiration_periods(): void
    {
        Notification::fake();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Expira en 7 días
        Subscription::factory()->create([
            'user_id' => $user1->id,
            'status' => 'active',
            'ends_at' => now()->addDays(7)->startOfDay(),
        ]);

        // Expira en 1 día
        Subscription::factory()->create([
            'user_id' => $user2->id,
            'status' => 'active',
            'ends_at' => now()->addDays(1)->startOfDay(),
        ]);

        $this->artisan('subscriptions:check-expired', ['--notify-days' => '7,3,1'])
            ->assertSuccessful();

        // Ambos usuarios deben recibir notificación
        Notification::assertSentTo(
            [$user1, $user2],
            \App\Notifications\SubscriptionExpiringSoon::class
        );
    }
}
