<?php

namespace App\Livewire;

use App\Services\NotificationPreferencesService;
use Livewire\Component;
use App\Livewire\Concerns\WithToastNotifications;

class NotificationPreferences extends Component
{
    use WithToastNotifications;

    public array $channels = [];
    public string $delivery = 'instant';
    public array $categories = [];

    public function mount(): void
    {
        $user = auth()->user();
        $effective = NotificationPreferencesService::getEffective($user);

        $this->channels = $effective['channels'];
        $this->delivery = $effective['delivery'];
        $this->categories = NotificationPreferencesService::getCategoriesForRole($user->role);
    }

    public function toggleChannel(string $category, string $channel): void
    {
        $current = $this->channels[$category] ?? [];

        if (in_array($channel, $current)) {
            // No permitir desactivar database (siempre debe quedar registro)
            if ($channel === 'database') {
                return;
            }
            $this->channels[$category] = array_values(array_diff($current, [$channel]));
        } else {
            $this->channels[$category] = array_merge($current, [$channel]);
        }
    }

    public function save(): void
    {
        NotificationPreferencesService::save(
            auth()->user(),
            $this->channels,
            $this->delivery
        );

        $this->toastSuccess('Preferencias de notificación guardadas');
    }

    public function render()
    {
        return view('livewire.notification-preferences');
    }
}
