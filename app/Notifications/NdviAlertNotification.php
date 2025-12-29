<?php

namespace App\Notifications;

use App\Models\Plot;
use App\Models\PlotRemoteSensing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notificación cuando el NDVI de una parcela baja significativamente
 */
class NdviAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Plot $plot,
        public PlotRemoteSensing $currentData,
        public ?PlotRemoteSensing $previousData = null
    ) {}

    /**
     * Canales de notificación
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Datos para almacenar en base de datos
     */
    public function toArray(object $notifiable): array
    {
        $change = 0;
        if ($this->previousData && $this->previousData->ndvi_mean > 0) {
            $change = (($this->currentData->ndvi_mean - $this->previousData->ndvi_mean) / $this->previousData->ndvi_mean) * 100;
        }

        return [
            'type' => 'ndvi_alert',
            'title' => '⚠️ Alerta NDVI - ' . $this->plot->name,
            'message' => $this->buildMessage($change),
            'plot_id' => $this->plot->id,
            'plot_name' => $this->plot->name,
            'ndvi_current' => $this->currentData->ndvi_mean,
            'ndvi_previous' => $this->previousData?->ndvi_mean,
            'change_percent' => round($change, 1),
            'health_status' => $this->currentData->health_status,
            'health_emoji' => $this->currentData->health_emoji,
            'image_date' => $this->currentData->image_date->toDateString(),
            'url' => route('plots.show', $this->plot),
            'severity' => $this->getSeverity(),
        ];
    }

    /**
     * Construir mensaje de la notificación
     */
    private function buildMessage(float $change): string
    {
        $status = match ($this->currentData->health_status) {
            'poor' => 'Bajo',
            'critical' => 'Crítico',
            default => $this->currentData->health_status,
        };

        if ($this->previousData && abs($change) > 5) {
            return sprintf(
                'El NDVI de "%s" ha bajado un %.1f%% (de %.2f a %.2f). Estado: %s. Se recomienda revisar la parcela.',
                $this->plot->name,
                abs($change),
                $this->previousData->ndvi_mean,
                $this->currentData->ndvi_mean,
                $status
            );
        }

        return sprintf(
            'La parcela "%s" muestra un NDVI bajo (%.2f). Estado: %s. Se recomienda revisar la parcela.',
            $this->plot->name,
            $this->currentData->ndvi_mean,
            $status
        );
    }

    /**
     * Determinar severidad de la alerta
     */
    private function getSeverity(): string
    {
        return match ($this->currentData->health_status) {
            'critical' => 'high',
            'poor' => 'medium',
            default => 'low',
        };
    }
}
