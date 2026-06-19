<?php

namespace App\Livewire\Winery\Wines\Traits;

use App\Models\WineFermentationControl;
use Illuminate\Support\Facades\Auth;

trait WithFermentationControlForm
{
    public string $fc_container_id = '';

    public string $fc_control_date = '';

    public string $fc_temperature = '';

    public string $fc_brix = '';

    public string $fc_density = '';

    public string $fc_ph = '';

    public string $fc_va = '';

    public string $fc_notes = '';

    public function saveFermentationControl(): void
    {
        $this->validate([
            'fc_container_id' => $this->ownedContainerRule(),
            'fc_control_date' => ['required', 'date'],
            'fc_temperature' => ['nullable', 'numeric', 'min:-20', 'max:100'],
            'fc_brix' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fc_density' => ['nullable', 'numeric', 'min:0.900', 'max:1.200'],
            'fc_ph' => ['nullable', 'numeric', 'min:2', 'max:7'],
            'fc_va' => ['nullable', 'numeric', 'min:0'],
            'fc_notes' => ['nullable', 'string'],
        ]);

        WineFermentationControl::create([
            'wine_id' => $this->wine->id,
            'container_id' => $this->fc_container_id,
            'control_date' => $this->fc_control_date,
            'temperature' => $this->fc_temperature ?: null,
            'brix_degree' => $this->fc_brix ?: null,
            'density' => $this->fc_density ?: null,
            'ph' => $this->fc_ph ?: null,
            'volatile_acidity' => $this->fc_va ?: null,
            'notes' => $this->fc_notes ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->resetFcForm();
        $this->dispatch('close-modal', id: 'modal-fermentation');
        $this->toastSuccess(__('Control de fermentación registrado.'));
    }

    public function deleteFermentationControl(int $id): void
    {
        WineFermentationControl::where('wine_id', $this->wine->id)->findOrFail($id)->delete();
        $this->toastSuccess(__('Control eliminado.'));
    }

    private function resetFcForm(): void
    {
        $this->fc_container_id = '';
        $this->fc_control_date = now()->format('Y-m-d\TH:i');
        $this->fc_temperature = $this->fc_brix = $this->fc_density = '';
        $this->fc_ph = $this->fc_va = $this->fc_notes = '';
    }
}
