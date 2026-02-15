<?php

namespace App\Listeners;

use App\Events\OfficialReportGenerated;
use Illuminate\Support\Facades\Log;
use App\Services\SecurityLogger;

class LogReportGeneration
{
    /**
     * Handle the event.
     */
    public function handle(OfficialReportGenerated $event): void
    {
        $report = $event->report;

        Log::info('Informe oficial generado', [
            'report_id' => $report->id,
            'report_type' => $report->report_type,
            'user_id' => $report->user_id,
            'verification_code' => $report->verification_code,
        ]);

        // Log de seguridad para auditoría
        SecurityLogger::log([
            'event' => 'official_report_generated',
            'user_id' => $report->user_id,
            'report_id' => $report->id,
            'report_type' => $report->report_type,
            'verification_code' => $report->verification_code,
        ]);
    }
}
