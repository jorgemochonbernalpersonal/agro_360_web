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
 * Notifica a la BODEGA cuando el supervisor aprueba o rechaza su solicitud.
 */
class SupervisorRequestResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected SupervisorRequest $supervisorRequest,
        protected string $resolution  // 'approved' | 'rejected'
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
        $supervisor = $req->supervisor;
        $typeLabel = __(SupervisorRequest::TYPE_LABELS[$req->type] ?? $req->type);
        $approved = $this->resolution === SupervisorRequest::STATUS_APPROVED;
        $url = AppLink::url(route('winery.denomination.requests.index'), 'agro365://home');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        $subject = $approved
            ? '✅ Solicitud aprobada por tu DO — '.$typeLabel
            : '❌ Solicitud no aprobada por tu DO — '.$typeLabel;

        return (new MailMessage)
            ->subject($subject)
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?: '']))
            ->line(
                $approved
                    ? 'Tu Denominación de Origen **'.$supervisor->name.'** ha **aprobado** tu solicitud.'
                    : 'Tu Denominación de Origen **'.$supervisor->name.'** ha **rechazado** tu solicitud.'
            )
            ->line('**Tipo:** '.$typeLabel)
            ->when($req->title, fn ($m) => $m->line('**Asunto:** '.$req->title))
            ->action(__('Ver detalle'), $url)
            ->when(
                ! $approved,
                fn ($m) => $m->line(__('Si tienes dudas sobre la resolución, contacta directamente con tu denominación de origen.'))
            )
            ->salutation(__('Saludos,\nAgro365'));
    }

    public function toArray(object $notifiable): array
    {
        $req = $this->supervisorRequest;
        $approved = $this->resolution === SupervisorRequest::STATUS_APPROVED;
        $typeLabel = __(SupervisorRequest::TYPE_LABELS[$req->type] ?? $req->type);

        return [
            'request_id' => $req->id,
            'request_type' => $req->type,
            'request_title' => $req->title,
            'resolution' => $this->resolution,
            'supervisor_id' => $req->supervisor_id,
            'supervisor_name' => $req->supervisor?->name,
            'icon' => $approved ? '✅' : '❌',
            'message' => ($approved ? 'Aprobada' : 'Rechazada').': '.$typeLabel,
            'action_url' => route('winery.denomination.requests.index'),
            'action_text' => __('Ver detalle'),
        ];
    }
}
