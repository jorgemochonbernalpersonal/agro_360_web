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
 * Notifica a la BODEGA cuando su solicitud está próxima a vencer.
 */
class SupervisorRequestDueSoonNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    public function __construct(
        protected SupervisorRequest $supervisorRequest,
        protected int $daysLeft
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
        $typeLabel = __(SupervisorRequest::TYPE_LABELS[$req->type] ?? $req->type);
        $url       = AppLink::url(route('winery.denomination.requests.index'), 'agro365://home');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        $daysText = $this->daysLeft === 1 ? 'mañana' : "en {$this->daysLeft} días";

        return (new MailMessage)
            ->subject("⏰ Solicitud vence {$daysText} — {$typeLabel}")
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?: '']))
            ->line("Tu solicitud de tipo **{$typeLabel}** vence **{$daysText}** ({$req->due_date->format('d/m/Y')}).")
            ->when($req->title, fn ($m) => $m->line('**Asunto:** ' . $req->title))
            ->line(__('Si aún no has respondido, hazlo antes de que se supere la fecha límite.'))
            ->action(__('Responder ahora'), $url)
            ->salutation(__('Saludos,\nAgro365'));
    }

    public function toArray(object $notifiable): array
    {
        $req       = $this->supervisorRequest;
        $typeLabel = __(SupervisorRequest::TYPE_LABELS[$req->type] ?? $req->type);
        $daysText  = $this->daysLeft === 1 ? 'mañana' : "en {$this->daysLeft} días";

        return [
            'request_id'   => $req->id,
            'request_type' => $req->type,
            'request_title'=> $req->title,
            'due_date'     => $req->due_date->format('Y-m-d'),
            'days_left'    => $this->daysLeft,
            'icon'         => '⏰',
            'message'      => "Vence {$daysText}: {$typeLabel}",
            'action_url'   => route('winery.denomination.requests.index'),
            'action_text'  => __('Ver solicitud'),
        ];
    }
}
