<?php

namespace App\Livewire;

use App\Services\DashboardAlertsService;
use Livewire\Component;
use Illuminate\Notifications\DatabaseNotification;

class Notifications extends Component
{
    public $notifications = [];
    public $dashboardAlerts = [];
    public $unreadCount = 0;
    public $showDropdown = false;

    protected $listeners = ['notificationReceived' => 'loadNotifications'];

    public function mount()
    {
        $this->loadNotifications();
        $this->loadDashboardAlerts();
    }

    public function loadNotifications()
    {
        $this->notifications = auth()->user()
            ->notifications()
            ->latest()
            ->take(10)
            ->get();
        
        $this->unreadCount = auth()->user()->unreadNotifications()->count();
        
        // Add dashboard alerts count
        $this->unreadCount += count($this->dashboardAlerts);
    }

    public function loadDashboardAlerts()
    {
        $alertsService = new DashboardAlertsService();
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

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function render()
    {
        return view('livewire.notifications');
    }
}
