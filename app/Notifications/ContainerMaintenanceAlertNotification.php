<?php

namespace App\Notifications;

use App\Notifications\Concerns\RespectsPreferences;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class ContainerMaintenanceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsPreferences;

    /**
     * @param Collection $overdue  Mantenimientos vencidos (scheduled_date < hoy, status = scheduled)
     * @param Collection $upcoming Mantenimientos próximos (next_maintenance_date en los próximos 7 días)
     */
    public function __construct(
        protected Collection $overdue,
        protected Collection $upcoming,
    ) {}

    public function notificationCategory(): string
    {
        return 'container_maintenance';
    }

    public function via(object $notifiable): array
    {
        return $this->filterChannelsByPreferences($notifiable, ['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $overdueCount = $this->overdue->count();
        $upcomingCount = $this->upcoming->count();
        $total = $overdueCount + $upcomingCount;

        $subject = $total === 1
            ? 'Aviso de mantenimiento de contenedor'
            : "{$total} avisos de mantenimiento de contenedores";

        $url = AppLink::url(route('winery.containers.index'), 'agro365://containers');
        if (app()->environment('production')) {
            $url = str_replace('http://', 'https://', $url);
        }

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting(__('Hola :name', ['name' => $notifiable->name ?: '']));

        if ($overdueCount > 0) {
            $rows = $this->overdue->map(fn ($m) => '<tr>
                    <td style="padding:6px 8px;border-bottom:1px solid #e4e4e7;">'.e($m->container->name).'</td>
                    <td style="padding:6px 8px;border-bottom:1px solid #e4e4e7;">'.e($m->getTypeLabel()).'</td>
                    <td style="padding:6px 8px;border-bottom:1px solid #e4e4e7;color:#dc2626;">'.e($m->scheduled_date?->format('d/m/Y')).'</td>
                </tr>'
            )->implode('');

            $mail->line(new HtmlString(
                '<p style="margin:0 0 8px 0;font-weight:600;color:#dc2626;">⚠️ '.$overdueCount.' mantenimiento'.($overdueCount > 1 ? 's vencidos' : ' vencido').'</p>
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#fef2f2;">
                            <th style="text-align:left;padding:6px 8px;border-bottom:2px solid #fca5a5;">Contenedor</th>
                            <th style="text-align:left;padding:6px 8px;border-bottom:2px solid #fca5a5;">Tipo</th>
                            <th style="text-align:left;padding:6px 8px;border-bottom:2px solid #fca5a5;">Fecha prevista</th>
                        </tr>
                    </thead>
                    <tbody>'.$rows.'</tbody>
                </table>'
            ));
        }

        if ($upcomingCount > 0) {
            $rows = $this->upcoming->map(fn ($c) => '<tr>
                    <td style="padding:6px 8px;border-bottom:1px solid #e4e4e7;">'.e($c->name).'</td>
                    <td style="padding:6px 8px;border-bottom:1px solid #e4e4e7;color:#d97706;">'.e($c->next_maintenance_date?->format('d/m/Y')).'</td>
                </tr>'
            )->implode('');

            $mail->line(new HtmlString(
                '<p style="margin:16px 0 8px 0;font-weight:600;color:#d97706;">🔔 '.$upcomingCount.' contenedor'.($upcomingCount > 1 ? 'es con mantenimiento próximo' : ' con mantenimiento próximo').' (próximos 7 días)</p>
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#fffbeb;">
                            <th style="text-align:left;padding:6px 8px;border-bottom:2px solid #fcd34d;">Contenedor</th>
                            <th style="text-align:left;padding:6px 8px;border-bottom:2px solid #fcd34d;">Próximo mantenimiento</th>
                        </tr>
                    </thead>
                    <tbody>'.$rows.'</tbody>
                </table>'
            ));
        }

        return $mail
            ->action(__('Ver mis contenedores'), $url)
            ->line(__('Accede a tu panel de bodega para gestionar los mantenimientos.'))
            ->salutation(__('Saludos,\nAgro365'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'overdue_count' => $this->overdue->count(),
            'upcoming_count' => $this->upcoming->count(),
        ];
    }
}
