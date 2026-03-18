<?php

namespace App\Livewire\Viticulturist\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\WithToastNotifications;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $filter = 'all'; // all | unread

    public function markRead(string $id): void
    {
        Auth::user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        $this->toastSuccess('Notificación marcada como leída.');
    }

    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        $this->toastSuccess('Todas las notificaciones marcadas como leídas.');
    }

    public function delete(string $id): void
    {
        Auth::user()->notifications()->where('id', $id)->delete();
        $this->toastSuccess('Notificación eliminada.');
    }

    public function clearAll(): void
    {
        Auth::user()->notifications()->delete();
        $this->toastSuccess('Historial de notificaciones eliminado.');
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Auth::user()->notifications();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->latest()->paginate(20);
        $unreadCount   = Auth::user()->unreadNotifications()->count();

        return view('livewire.viticulturist.notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ])->layout('layouts.app');
    }
}
