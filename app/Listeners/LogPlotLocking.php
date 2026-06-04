<?php

namespace App\Listeners;

use App\Events\PlotLocked;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Log;

class LogPlotLocking
{
    /**
     * Handle the event.
     */
    public function handle(PlotLocked $event): void
    {
        $plot = $event->plot;

        Log::info('Parcela bloqueada', [
            'plot_id' => $plot->id,
            'plot_name' => $plot->name,
            'locked_by' => $plot->locked_by,
            'reason' => $event->reason,
        ]);

        // Log de seguridad
        SecurityLogger::log([
            'event' => 'plot_locked',
            'plot_id' => $plot->id,
            'locked_by' => $plot->locked_by,
            'reason' => $event->reason,
        ]);
    }
}
