<?php

namespace Tests\Feature\Viticulturist\Notifications;

use App\Livewire\Viticulturist\Notifications\Index;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Livewire;
use Tests\Feature\ViticulturistTestCase;

class IndexTest extends ViticulturistTestCase
{
    public function test_viticulturist_can_view_notifications(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertStatus(200);
    }

    public function test_shows_empty_state_when_no_notifications(): void
    {
        $viticulturist = $this->makeViticulturist();
        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->assertSee('Sin notificaciones');
    }

    public function test_mark_single_notification_as_read(): void
    {
        $viticulturist = $this->makeViticulturist();

        $notification = DatabaseNotification::create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\Notifications\TestNotification',
            'notifiable_type' => get_class($viticulturist),
            'notifiable_id'   => $viticulturist->id,
            'data'            => ['title' => 'Test', 'body' => 'Mensaje test'],
            'read_at'         => null,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('markRead', $notification->id);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_notifications_as_read(): void
    {
        $viticulturist = $this->makeViticulturist();

        foreach (range(1, 3) as $i) {
            DatabaseNotification::create([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'App\Notifications\TestNotification',
                'notifiable_type' => get_class($viticulturist),
                'notifiable_id'   => $viticulturist->id,
                'data'            => ['title' => "Notif {$i}", 'body' => 'test'],
                'read_at'         => null,
            ]);
        }

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('markAllRead');

        $this->assertEquals(
            0,
            $viticulturist->fresh()->unreadNotifications()->count()
        );
    }

    public function test_delete_notification(): void
    {
        $viticulturist = $this->makeViticulturist();

        $notification = DatabaseNotification::create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\Notifications\TestNotification',
            'notifiable_type' => get_class($viticulturist),
            'notifiable_id'   => $viticulturist->id,
            'data'            => ['title' => 'Para borrar', 'body' => 'test'],
            'read_at'         => null,
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->call('delete', $notification->id);

        $this->assertNull(DatabaseNotification::find($notification->id));
    }

    public function test_filter_unread_shows_only_unread(): void
    {
        $viticulturist = $this->makeViticulturist();

        DatabaseNotification::create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\Notifications\TestNotification',
            'notifiable_type' => get_class($viticulturist),
            'notifiable_id'   => $viticulturist->id,
            'data'            => ['title' => 'No leída', 'body' => 'test'],
            'read_at'         => null,
        ]);

        DatabaseNotification::create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\Notifications\TestNotification',
            'notifiable_type' => get_class($viticulturist),
            'notifiable_id'   => $viticulturist->id,
            'data'            => ['title' => 'Leída', 'body' => 'test'],
            'read_at'         => now(),
        ]);

        $this->actingAs($viticulturist);

        Livewire::test(Index::class)
            ->set('filter', 'unread')
            ->assertViewHas('notifications', fn($n) => $n->total() === 1);
    }
}
