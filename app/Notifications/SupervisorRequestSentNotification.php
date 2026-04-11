<?php

namespace App\Notifications;

use App\Models\SupervisorRequest;
use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica a la BODEGA cuando el supervisor le envía una solicitud/acta.
 */
class SupervisorRequestSentNotification extends Notification
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
        $req        = $this->supervisorRequest;
        $supervisor = $req->supervisor;
        $typeLabel  = SupervisorRequest::TYPE_LABELS[$req->type] ?? $req->type;
        $url        = AppLink::url(route('winery.denomination.requests.index'), 'agro365://home');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        $isNonconformity = $req->type === SupervisorRequest::TYPE_NONCONFORMITY;

        return (new MailMessage)
            ->subject(($isNonconformity ? '⚠️ Acta de no conformidad' : 'Nueva actuación de tu DO') . ' — ' . $supervisor->name)
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line('Tu Denominación de Origen **' . $supervisor->name . '** te ha enviado una actuación que requiere tu atención.')
            ->line('**Tipo:** ' . $typeLabel)
            ->when($req->title, fn ($m) => $m->line('**Asunto:** ' . $req->title))
            ->when($req->notes, fn ($m) => $m->line('**Notas:** ' . $req->notes))
            ->action('Ver actuación', $url)
            ->when(
                $isNonconformity,
                fn ($m) => $m->line('⚠️ Esta acta requiere respuesta. Por favor revisa los detalles y responde en el plazo indicado por tu DO.'),
                fn ($m) => $m->line('Puedes responder a esta actuación desde tu panel de denominación de origen.')
            )
            ->salutation("Saludos,\nAgro365");
    }

    public function toArray(object $notifiable): array
    {
        $req       = $this->supervisorRequest;
        $typeLabel = SupervisorRequest::TYPE_LABELS[$req->type] ?? $req->type;

        return [
            'request_id'      => $req->id,
            'request_type'    => $req->type,
            'request_title'   => $req->title,
            'supervisor_id'   => $req->supervisor_id,
            'supervisor_name' => $req->supervisor?->name,
            'icon'            => $req->type === SupervisorRequest::TYPE_NONCONFORMITY ? '⚠️' : '📋',
            'message'         => ($req->supervisor?->name ?? 'Tu DO') . ' — ' . $typeLabel,
            'action_url'      => route('winery.denomination.requests.index'),
            'action_text'     => 'Ver solicitud',
        ];
    }
}
