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
        $this->reportToInvalidate = OfficialReport::findOrFail($reportId);

        if ($this->reportToInvalidate->user_id !== auth()->id()) {
            $this->addError('invalidate', __('No tienes permiso para invalidar este informe.'));

            return;
        }

        if (! $this->reportToInvalidate->isValid()) {
            $this->addError('invalidate', __('Este informe ya está invalidado.'));

            return;
        }

        if (! $this->reportToInvalidate->canBeInvalidated()) {
            $maxDays = config('reports.max_days_to_invalidate', 30);
            $daysSinceSigned = $this->reportToInvalidate->signed_at->diffInDays(now());
            $this->addError('invalidate', __('Este informe no puede ser invalidado. Han pasado :days días desde su firma. Solo se pueden invalidar informes con menos de :max días.', ['days' => $daysSinceSigned, 'max' => $maxDays]));

            return;
        }

        $this->showInvalidateModal = true;
    }

    public function invalidateReport(): void
    {
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
