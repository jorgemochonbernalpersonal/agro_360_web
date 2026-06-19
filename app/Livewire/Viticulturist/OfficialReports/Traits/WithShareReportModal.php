<?php

namespace App\Livewire\Viticulturist\OfficialReports\Traits;

use App\Models\OfficialReport;

trait WithShareReportModal
{
    public $showShareModal = false;

    public $reportToShare = null;

    public $shareEmail = '';

    public $shareMessage = '';

    public function openShareModal($reportId): void
    {
        $this->reportToShare = OfficialReport::findOrFail($reportId);

        if ($this->reportToShare->user_id !== auth()->id()) {
            $this->addError('share', __('No tienes permiso para compartir este informe.'));

            return;
        }

        $this->showShareModal = true;
    }

    public function shareReport(): void
    {
        $this->validate([
            'shareEmail' => 'required|email',
            'shareMessage' => 'nullable|string|max:500',
        ], [
            'shareEmail.required' => __('El email es obligatorio.'),
            'shareEmail.email' => __('Introduce un email válido.'),
            'shareMessage.max' => __('El mensaje no puede superar 500 caracteres.'),
        ]);

        try {
            \Mail::to($this->shareEmail)->send(
                new \App\Mail\OfficialReportShared(
                    $this->reportToShare,
                    $this->shareMessage ?? 'Te comparto este informe oficial.',
                    auth()->user()->name
                )
            );

            $this->closeShareModal();
            $this->toastSuccess(__('Informe compartido exitosamente a :email.', ['email' => $this->shareEmail]));
        } catch (\Exception $e) {
            $this->addError('share', __('Error al enviar email: :message', ['message' => $e->getMessage()]));
        }
    }

    public function closeShareModal(): void
    {
        $this->showShareModal = false;
        $this->reportToShare = null;
        $this->shareEmail = '';
        $this->shareMessage = '';
        $this->resetValidation(['shareEmail', 'shareMessage']);
    }
}
