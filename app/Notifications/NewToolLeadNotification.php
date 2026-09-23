<?php

namespace App\Notifications;

use App\Http\Controllers\ToolsController;
use App\Models\ToolLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso interno al equipo cuando alguien deja su email en una herramienta pública.
 * Se envía a una ruta on-demand (config support.email), no a un User.
 */
class NewToolLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected ToolLead $lead
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;
        $toolTitle = ToolsController::TOOLS[$lead->tool]['title'] ?? $lead->tool;

        $mail = (new MailMessage)
            ->subject('Nuevo contacto desde herramientas: '.$lead->email)
            ->greeting('Nuevo contacto')
            ->line('**Email:** '.$lead->email)
            ->line('**Nombre:** '.($lead->name ?: '—'))
            ->line('**Perfil:** '.$lead->profileLabel())
            ->line('**Herramienta:** '.$toolTitle);

        if (isset($lead->context['score'])) {
            $mail->line('**Nota del test 2027:** '.$lead->context['score'].' %');
        }

        return $mail
            ->action('Ver contactos', route('admin.leads.index'))
            ->line('Escríbele pronto: un contacto de hace una hora vale más que uno de hace una semana.')
            ->salutation('Agro365');
    }
}
