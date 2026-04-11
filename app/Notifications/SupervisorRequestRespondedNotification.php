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
 * Notifica al SUPERVISOR cuando una bodega responde a su solicitud.
 */
class SupervisorRequestRespondedNotification extends Notification implements ShouldQueue
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
            ->subject('Respuesta recibida de ' . $winery->name . ' — ' . $typeLabel)
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('La bodega **' . $winery->name . '** ha respondido a tu solicitud.')
            ->line('**Tipo:** ' . $typeLabel)
            ->when($req->title, fn ($m) => $m->line('**Asunto:** ' . $req->title))
            ->when($req->response_notes, fn ($m) => $m->line('**Respuesta:** ' . $req->response_notes))
            ->action('Ver solicitudes', $url)
            ->line('La solicitud queda en estado "En revisión". Puedes aprobarla o rechazarla desde tu panel.')
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
            'response'     => $req->response_notes,
            'icon'         => '💬',
            'message'      => ($req->winery?->name ?? 'Bodega') . ' respondió — ' . $typeLabel,
            'action_url'   => route('supervisor.requests.index'),
            'action_text'  => 'Revisar respuesta',
        ];
    }
}
