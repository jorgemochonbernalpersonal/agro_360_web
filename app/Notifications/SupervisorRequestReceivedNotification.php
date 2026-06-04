<?php

namespace App\Notifications;

use App\Models\SupervisorRequest;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        $req = $this->supervisorRequest;
        $winery = $req->winery;
        $typeLabel = __(SupervisorRequest::TYPE_LABELS[$req->type] ?? $req->type);
        $url = AppLink::url(route('supervisor.requests.index'), 'agro365://home');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        return (new MailMessage)
            ->subject(__('Nueva solicitud de :winery — :type', ['winery' => $winery->name, 'type' => $typeLabel]))
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?: '']))
            ->line(__('La bodega **:winery** ha enviado una solicitud a tu denominación de origen.', ['winery' => $winery->name]))
            ->line('**Tipo:** '.$typeLabel)
            ->when($req->title, fn ($m) => $m->line('**Asunto:** '.$req->title))
            ->when($req->notes, fn ($m) => $m->line('**Descripción:** '.$req->notes))
            ->action(__('Ver solicitudes'), $url)
            ->line(__('Revisa la solicitud y responde desde tu panel de control.'))
            ->salutation(__('Saludos,\nAgro365'));
    }

    public function toArray(object $notifiable): array
    {
        $req = $this->supervisorRequest;
        $typeLabel = __(SupervisorRequest::TYPE_LABELS[$req->type] ?? $req->type);

        return [
            'request_id' => $req->id,
            'request_type' => $req->type,
            'request_title' => $req->title,
            'winery_id' => $req->winery_id,
            'winery_name' => $req->winery?->name,
            'icon' => '📩',
            'message' => ($req->winery?->name ?? 'Bodega').' — '.$typeLabel,
            'action_url' => route('supervisor.requests.index'),
            'action_text' => __('Ver solicitudes'),
        ];
    }
}
