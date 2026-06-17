<?php

namespace App\Notifications;

use App\Models\OfficialReport;
use App\Support\AppLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public OfficialReport $report
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $reportType = match ($this->report->report_type) {
            'phytosanitary_treatments' => __('Tratamientos Fitosanitarios'),
            default => __('Cuaderno de Campo Completo')
        };

        return (new MailMessage)
            ->subject("Informe generado - {$reportType}")
            ->greeting('¡Tu informe está listo!')
            ->line("Tu informe de **{$reportType}** ha sido generado exitosamente.")
            ->line('**Código de verificación:** '.$this->report->verification_code)
            ->line(__('Este código permite verificar la autenticidad del documento.'))
            ->action(__('Descargar informe'), AppLink::url(route('reports.download', $this->report), 'agro365://home'))
            ->line(__('El informe incluye firma digital y puede ser verificado oficialmente.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'report_type' => $this->report->report_type,
            'verification_code' => $this->report->verification_code,
            'generated_at' => $this->report->created_at->toIso8601String(),
        ];
    }
}
