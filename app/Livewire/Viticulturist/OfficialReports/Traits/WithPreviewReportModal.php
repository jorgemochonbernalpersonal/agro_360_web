<?php

namespace App\Livewire\Viticulturist\OfficialReports\Traits;

use App\Models\OfficialReport;
use RuntimeException;

trait WithPreviewReportModal
{
    public $showPreviewModal = false;

    public $reportToPreview = null;

    public function openPreviewModal($reportId): void
    {
        $this->reportToPreview = OfficialReport::findOrFail($reportId);

        if ($this->reportToPreview->user_id !== auth()->id()) {
            $this->addError('preview', __('No tienes permiso para ver este informe.'));

            return;
        }

        $this->showPreviewModal = true;
    }

    public function closePreviewModal(): void
    {
        $this->showPreviewModal = false;
        $this->reportToPreview = null;
    }

    public function downloadInFormat($reportId, $format)
    {
        try {
            $report = OfficialReport::findOrFail($reportId);

            if ($report->user_id !== auth()->id()) {
                $this->toastError(__('No tienes permiso para descargar este informe.'));

                return;
            }

            if ($report->processing_status !== 'completed') {
                $this->toastWarning(__('Este informe aún está siendo procesado. Espera a que se complete.'));

                return;
            }

            $service = app(\App\Services\OfficialReportService::class);

            return $service->downloadReportInFormat($report, $format);
        } catch (\Exception $e) {
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : __('Error al descargar el informe. Inténtalo de nuevo.'));
        }
    }
}
