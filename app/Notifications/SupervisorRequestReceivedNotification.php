<?php

namespace App\Notifications;

use App\Models\SupervisorRequest;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica al SUPERVISOR cuando una bodega le envía una solicitud.
 */
class SupervisorRequestReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected SupervisorRequest $supervisorRequest
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
        $req       = $this->supervisorRequest;
        $winery    = $req->winery;
        $typeLabel = SupervisorRequest::TYPE_LABELS[$req->type] ?? $req->type;
        $url       = AppLink::url(route('supervisor.requests.index'), 'agro365://home');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        return (new MailMessage)
            ->subject('Nueva solicitud de ' . $winery->name . ' — ' . $typeLabel)
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('La bodega **' . $winery->name . '** ha enviado una solicitud a tu denominación de origen.')
            ->line('**Tipo:** ' . $typeLabel)
            ->when($req->title, fn ($m) => $m->line('**Asunto:** ' . $req->title))
            ->when($req->notes, fn ($m) => $m->line('**Descripción:** ' . $req->notes))
            ->action('Ver solicitudes', $url)
            ->line('Revisa la solicitud y responde desde tu panel de control.')
            ->salutation("Saludos,\nAgro365");
    }

    public function toArray(object $notifiable): array
    {
        $req       = $this->supervisorRequest;
        $typeLabel = SupervisorRequest::TYPE_LABELS[$req->type] ?? $req->type;

        return [
            'request_id'   => $req->id,
            'request_type' => $req->type,
            'request_title'=> $req->title,
            'winery_id'    => $req->winery_id,
            'winery_name'  => $req->winery?->name,
            'icon'         => '📩',
            'message'      => ($req->winery?->name ?? 'Bodega') . ' — ' . $typeLabel,
            'action_url'   => route('supervisor.requests.index'),
            'action_text'  => 'Ver solicitudes',
        ];
    }
}
