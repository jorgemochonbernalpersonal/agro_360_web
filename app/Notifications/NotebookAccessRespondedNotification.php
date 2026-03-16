<?php

namespace App\Notifications;

use App\Models\NotebookAccessRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotebookAccessRespondedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected User   $viticulturist,
        protected string $status, // approved | rejected
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('winery.viticulturists.index');

        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        $approved = $this->status === NotebookAccessRequest::STATUS_APPROVED;

        $subject = $approved
            ? 'Acceso al cuaderno aprobado — ' . $this->viticulturist->name
            : 'Solicitud de acceso al cuaderno rechazada — ' . $this->viticulturist->name;

        $body = $approved
            ? 'El viticultor **' . $this->viticulturist->name . '** ha aprobado tu solicitud. Ya puedes consultar su cuaderno de campo digital.'
            : 'El viticultor **' . $this->viticulturist->name . '** ha rechazado tu solicitud de acceso al cuaderno de campo.';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola ' . ($notifiable->name ?: ''))
            ->line($body)
            ->action('Ir a mis viticultores', $url)
            ->salutation("Saludos,\nAgro365");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'viticulturist_id'   => $this->viticulturist->id,
            'viticulturist_name' => $this->viticulturist->name,
            'status'             => $this->status,
        ];
    }
}
