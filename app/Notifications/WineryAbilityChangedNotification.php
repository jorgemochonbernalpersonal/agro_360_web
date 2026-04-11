<?php

namespace App\Notifications;

use App\Models\Ability;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica a la BODEGA cuando el supervisor activa o desactiva un módulo.
 */
class WineryAbilityChangedNotification extends Notification
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected Ability $ability,
        protected bool    $granted,       // true = activado, false = desactivado
        protected string  $supervisorName
    ) {}

    public function notificationCategory(): string
    {
        return 'do_requests';
    }

    public function via(object $notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = AppLink::url(route('winery.denomination.index'), 'agro365://home');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        $subject = $this->granted
            ? '✅ Módulo activado por tu DO — ' . $this->ability->name
            : '⚠️ Módulo desactivado por tu DO — ' . $this->ability->name;

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line(
                $this->granted
                    ? 'Tu Denominación de Origen **' . $this->supervisorName . '** ha **activado** el módulo **' . $this->ability->name . '** en tu cuenta.'
                    : 'Tu Denominación de Origen **' . $this->supervisorName . '** ha **desactivado** el módulo **' . $this->ability->name . '** en tu cuenta.'
            )
            ->when(
                $this->granted,
                fn ($m) => $m->line('Ya puedes acceder a este módulo desde tu panel.'),
                fn ($m) => $m->line('Si tienes dudas sobre este cambio, contacta con tu denominación de origen.')
            )
            ->action('Ver mi DO', $url)
            ->salutation("Saludos,\nAgro365");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ability_id'      => $this->ability->id,
            'ability_name'    => $this->ability->name,
            'ability_code'    => $this->ability->code,
            'granted'         => $this->granted,
            'supervisor_name' => $this->supervisorName,
            'icon'            => $this->granted ? '🔓' : '🔒',
            'message'         => ($this->granted ? 'Módulo activado' : 'Módulo desactivado') . ': ' . $this->ability->name,
            'action_url'      => route('winery.denomination.index'),
            'action_text'     => 'Ver mi DO',
        ];
    }
}
