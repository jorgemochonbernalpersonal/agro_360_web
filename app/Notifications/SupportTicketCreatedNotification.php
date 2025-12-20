<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use App\Models\SupportTicket;

class SupportTicketCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected SupportTicket $ticket
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $ticketUrl = route('viticulturist.support.index');
        
        // Solo forzar HTTPS en producción
        if (app()->environment('production')) {
            $ticketUrl = str_replace('http://', 'https://', $ticketUrl);
        }
        
        // Generar URL absoluta para la imagen
        $logoUrl = url('images/logo.png');
        
        // Solo forzar HTTPS en producción
        if (app()->environment('production')) {
            $logoUrl = str_replace('http://', 'https://', $logoUrl);
        }

        $typeLabels = [
            'bug' => '🐛 Bug',
            'feature' => '✨ Nueva Funcionalidad',
            'improvement' => '🚀 Mejora',
            'question' => '❓ Pregunta',
        ];

        $priorityLabels = [
            'low' => '⚪ Baja',
            'medium' => '🟡 Media',
            'high' => '🟠 Alta',
            'urgent' => '🔴 Urgente',
        ];

        $typeLabel = $typeLabels[$this->ticket->type] ?? $this->ticket->type;
        $priorityLabel = $priorityLabels[$this->ticket->priority] ?? $this->ticket->priority;

        return (new MailMessage)
            ->subject('Nuevo Ticket de Soporte - ' . $this->ticket->title)
            ->line(new HtmlString(
                '<div style="text-align:center; margin-bottom: 16px;">
                    <img src="'.$logoUrl.'" alt="Agro365"
                         style="max-width: 160px; height: auto;">
                 </div>'
            ))
            ->greeting('Hola ' . ($notifiable->name ?: 'Administrador'))
            ->line('Se ha creado un nuevo ticket de soporte en Agro365.')
            ->line(new HtmlString(
                '<div style="background-color: #f3f4f6; padding: 16px; border-radius: 8px; margin: 16px 0;">
                    <p style="margin: 0 0 8px 0;"><strong>Usuario:</strong> ' . e($this->ticket->user->name) . ' (' . e($this->ticket->user->email) . ')</p>
                    <p style="margin: 0 0 8px 0;"><strong>Título:</strong> ' . e($this->ticket->title) . '</p>
                    <p style="margin: 0 0 8px 0;"><strong>Tipo:</strong> ' . e($typeLabel) . '</p>
                    <p style="margin: 0 0 8px 0;"><strong>Prioridad:</strong> ' . e($priorityLabel) . '</p>
                    <p style="margin: 0;"><strong>Descripción:</strong></p>
                    <p style="margin: 8px 0 0 0; white-space: pre-wrap;">' . nl2br(e($this->ticket->description)) . '</p>' .
                    ($this->ticket->image ? '<p style="margin: 16px 0 0 0;"><strong>Imagen adjunta:</strong> <a href="' . url($this->ticket->image_url) . '" style="color: #059669; text-decoration: underline;">Ver imagen</a></p>' : '') .
                '</div>'
            ))
            ->action('Ver Ticket', $ticketUrl)
            ->line('Por favor, revisa el ticket y responde al usuario lo antes posible.')
            ->salutation("Saludos,\nEl equipo de Agro365");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'user_name' => $this->ticket->user->name,
            'type' => $this->ticket->type,
            'priority' => $this->ticket->priority,
        ];
    }
}

