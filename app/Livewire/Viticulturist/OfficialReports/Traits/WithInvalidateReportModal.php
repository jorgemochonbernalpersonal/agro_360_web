<?php

namespace App\Livewire\Viticulturist\OfficialReports\Traits;

use App\Models\OfficialReport;

trait WithInvalidateReportModal
{
    public $showInvalidateModal = false;

    public $reportToInvalidate = null;

    public $invalidatePassword = '';

    public $invalidateReason = '';

    public function openInvalidateModal($reportId): void
    {
        // Comprobar la propiedad ANTES de asignar a la propiedad pública (ver el
        // mismo fix en WithShareReportModal): si no, un informe ajeno queda cargado
        // en $reportToInvalidate aunque el mensaje de error diga que no se puede.
        $report = OfficialReport::where('user_id', auth()->id())->find($reportId);

        if (! $report) {
            $this->addError('invalidate', __('No tienes permiso para invalidar este informe.'));

            return;
        }

        if (! $report->isValid()) {
            $this->addError('invalidate', __('Este informe ya está invalidado.'));

            return;
        }

        if (! $report->canBeInvalidated()) {
            $maxDays = config('reports.max_days_to_invalidate', 30);
            $daysSinceSigned = $report->signed_at->diffInDays(now());
            $this->addError('invalidate', __('Este informe no puede ser invalidado. Han pasado :days días desde su firma. Solo se pueden invalidar informes con menos de :max días.', ['days' => $daysSinceSigned, 'max' => $maxDays]));

            return;
        }

        $this->reportToInvalidate = $report;
        $this->showInvalidateModal = true;
    }

    public function invalidateReport(): void
    {
        // Re-comprobar la propiedad aquí también, no fiarse solo de openInvalidateModal().
        if (! $this->reportToInvalidate instanceof OfficialReport || $this->reportToInvalidate->user_id !== auth()->id()) {
            $this->addError('invalidate', __('No tienes permiso para invalidar este informe.'));

            return;
        }

        $this->validate([
            'invalidatePassword' => 'required|string',
            'invalidateReason' => 'required|string|min:10',
        ], [
            'invalidatePassword.required' => __('La contraseña es obligatoria.'),
            'invalidateReason.required' => __('Debes especificar un motivo.'),
            'invalidateReason.min' => __('El motivo debe tener al menos 10 caracteres.'),
        ]);

        try {
            if (! \Hash::check($this->invalidatePassword, auth()->user()->password)) {
                $this->addError('invalidatePassword', __('Contraseña incorrecta.'));

                return;
            }

            $this->reportToInvalidate->invalidate($this->invalidateReason);
            $this->closeInvalidateModal();
            $this->toastSuccess(__('Informe invalidado correctamente.'));
        } catch (\Exception $e) {
            $this->addError('invalidate', __('Error al invalidar: :message', ['message' => $e->getMessage()]));
        }
    }

    public function closeInvalidateModal(): void
    {
        $this->showInvalidateModal = false;
        $this->reportToInvalidate = null;
        $this->invalidatePassword = '';
        $this->invalidateReason = '';
        $this->resetValidation(['invalidatePassword', 'invalidateReason']);
    }
}
