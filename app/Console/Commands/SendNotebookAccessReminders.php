<?php

namespace App\Console\Commands;

use App\Models\NotebookAccessRequest;
use Illuminate\Console\Command;
use Illuminate\Notifications\Messages\MailMessage;

class SendNotebookAccessReminders extends Command
{
    protected $signature = 'agro:notebook-access-reminders {--days=7 : Días sin respuesta para enviar recordatorio}';

    protected $description = 'Recuerda a viticultores las solicitudes de cuaderno de campo sin respuesta';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);
        $sent = 0;

        $pending = NotebookAccessRequest::where('status', NotebookAccessRequest::STATUS_PENDING)
            ->where('requested_at', '<=', $cutoff)
            ->with(['viticulturist:id,name,email', 'winery:id,name', 'supervisor:id,name'])
            ->get();

        // Group by viticulturist to send a single email per viticulturist with all pending requests
        $byViticulturist = $pending->groupBy('viticulturist_id');

        foreach ($byViticulturist as $viticulturistId => $requests) {
            $viticulturist = $requests->first()->viticulturist;

            if (! $viticulturist?->email || ! $viticulturist->can_login) {
                continue;
            }

            $requestorNames = $requests->map(function ($r) {
                return $r->winery->name ?? $r->supervisor->name ?? '—';
            })->implode(', ');

            $count = $requests->count();
            $label = $count === 1 ? '1 solicitud pendiente' : "{$count} solicitudes pendientes";

            $viticulturist->notify(
                new class($label, $requestorNames, $days) extends \Illuminate\Notifications\Notification
                {
                    use \Illuminate\Bus\Queueable;

                    public function __construct(
                        private string $label,
                        private string $requestorNames,
                        private int $days,
                    ) {}

                    public function via($n): array
                    {
                        return ['mail'];
                    }

                    public function toMail($notifiable): MailMessage
                    {
                        return (new MailMessage)
                            ->subject('Recordatorio — '.$this->label.' de acceso al cuaderno de campo')
                            ->greeting('Hola, '.$notifiable->name.':')
                            ->line('Tienes **'.$this->label.'** de acceso a tu cuaderno de campo sin responder desde hace más de '.$this->days.' días.')
                            ->line('**Solicitante(s):** '.$this->requestorNames)
                            ->line('Puedes aprobar o rechazar estas solicitudes desde tu panel de Agro365.')
                            ->action('Gestionar solicitudes', route('viticulturist.winery-access.index'))
                            ->line('Tu cuaderno de campo es privado por defecto. Solo tú decides quién tiene acceso.');
                    }
                }
            );

            $sent++;
            $this->line("✓ Recordatorio enviado a {$viticulturist->email} ({$label})");
        }

        $this->info("Recordatorios de cuaderno enviados: {$sent}");

        return self::SUCCESS;
    }
}
