<?php

namespace App\Livewire\Viticulturist\BodegaMessages;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function markRead(string $id): void
    {
        Auth::user()->notifications()->where('id', $id)->update(['read_at' => now()]);
    }

    public function render()
    {
        $notifications = Auth::user()
            ->notifications()
            ->whereIn('type', [
                \App\Notifications\HarvestDeliveryMatchedNotification::class,
                \App\Notifications\HarvestDeliveryAutoDisputedNotification::class,
                \App\Notifications\HarvestDeliveryResolvedNotification::class,
                \App\Notifications\GrapePurchaseInvoiceIssuedNotification::class,
            ])
            ->paginate(20);

        $unreadCount = Auth::user()
            ->unreadNotifications()
            ->whereIn('type', [
                \App\Notifications\HarvestDeliveryMatchedNotification::class,
                \App\Notifications\HarvestDeliveryAutoDisputedNotification::class,
                \App\Notifications\HarvestDeliveryResolvedNotification::class,
                \App\Notifications\GrapePurchaseInvoiceIssuedNotification::class,
            ])
            ->count();

        return view('livewire.viticulturist.bodega-messages.index', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ])->layout('layouts.app');
    }
}
