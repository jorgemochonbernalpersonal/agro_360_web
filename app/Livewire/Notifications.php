<?php

namespace App\Livewire;

use App\Services\DashboardAlertsService;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\On;
use Livewire\Component;

class Notifications extends Component
{
    public $notifications = [];

    public $dashboardAlerts = [];

    public $unreadCount = 0;

    protected $listeners = ['notificationReceived' => 'loadNotifications'];

    /** @deprecated Alpine manages open state locally — no server sync needed */
    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->loadDashboardAlerts();

        $this->notifications = auth()->user()
            ->notifications()
            ->latest()
            ->take(10)
            ->get();

        $this->unreadCount = auth()->user()->unreadNotifications()->count();
        $this->unreadCount += count($this->dashboardAlerts);
    }

    public function loadDashboardAlerts()
    {
        $alertsService = new DashboardAlertsService;
        $this->dashboardAlerts = $alertsService->getAlerts(auth()->user())->toArray();
    }

    public function markAsRead($notificationId)
    {
        $notification = DatabaseNotification::find($notificationId);

        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function deleteNotification($notificationId)
    {
        $notification = DatabaseNotification::find($notificationId);

        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->delete();
            $this->loadNotifications();
        }
    }

    /**
     * Guess notification icon based on notification type or data keys.
     */
    public function guessIcon(array $data): string
    {
        if (isset($data['plot_id']) || isset($data['ndvi'])) {
            return '📉';
        }
        if (isset($data['treatment_id']) || isset($data['safe_date'])) {
            return '🧪';
        }
        if (isset($data['payment_id'])) {
            return '💳';
        }
        if (isset($data['export_type'])) {
            return '📦';
        }
        if (isset($data['report_id']) && isset($data['error_message'])) {
            return '❌';
        }
        if (isset($data['report_id'])) {
            return '📄';
        }
        if (isset($data['delivery_id']) || str_contains($data['message'] ?? '', 'entrega')) {
            return '🍇';
        }
        if (isset($data['days_remaining'])) {
            return '📅';
        }

        return '🔔';
    }

    /**
     * Guess icon color class based on notification data.
     */
    public function guessIconColor(array $data): string
    {
        if (isset($data['error_message'])) {
            return 'text-red-500';
        }
        if (isset($data['ndvi']) || isset($data['alert_type'])) {
            return 'text-amber-500';
        }
        if (isset($data['payment_id'])) {
            return 'text-green-500';
        }

        return '';
    }

    public function render()
    {
        return view('livewire.notifications');
    }
}
