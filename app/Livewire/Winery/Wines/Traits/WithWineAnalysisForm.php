<?php

namespace App\Livewire\Winery\Wines\Traits;

use App\Models\WineAnalysis;
use Illuminate\Support\Facades\Auth;

trait WithWineAnalysisForm
{
    public string $an_container_id = '';

    public string $an_oenologist_id = '';

    public string $an_date = '';

    public string $an_type = 'own';

    public string $an_laboratory = '';

    public string $an_alcohol = '';

    public string $an_residual_sugar = '';

    public string $an_total_acidity = '';

    public string $an_volatile_acidity = '';

    public string $an_ph = '';

    public string $an_so2_free = '';

    public string $an_so2_total = '';

    public string $an_density = '';

    public string $an_turbidity = '';

    public string $an_color_intensity = '';

    public string $an_malic_acid = '';

    public string $an_notes = '';

    public function saveAnalysis(): void
    {
        $this->validate([
            'an_container_id' => $this->ownedContainerRule(false),
            'an_oenologist_id' => $this->ownedOenologistRule(),
            'an_date' => ['required', 'date'],
            'an_type' => ['required', 'in:own,external'],
            'an_laboratory' => ['nullable', 'string', 'max:200'],
            'an_alcohol' => ['nullable', 'numeric', 'min:0', 'max:25'],
            'an_residual_sugar' => ['nullable', 'numeric', 'min:0'],
            'an_total_acidity' => ['nullable', 'numeric', 'min:0'],
            'an_volatile_acidity' => ['nullable', 'numeric', 'min:0'],
            'an_ph' => ['nullable', 'numeric', 'min:2', 'max:7'],
            'an_so2_free' => ['nullable', 'numeric', 'min:0'],
            'an_so2_total' => ['nullable', 'numeric', 'min:0'],
            'an_density' => ['nullable', 'numeric', 'min:0.900', 'max:1.200'],
            'an_turbidity' => ['nullable', 'numeric', 'min:0'],
            'an_color_intensity' => ['nullable', 'numeric', 'min:0'],
            'an_malic_acid' => ['nullable', 'numeric', 'min:0'],
            'an_notes' => ['nullable', 'string'],
        ]);

        WineAnalysis::create([
            'wine_id' => $this->wine->id,
            'container_id' => $this->an_container_id ?: null,
            'oenologist_id' => $this->an_oenologist_id ?: null,
            'analysis_date' => $this->an_date,
            'analysis_type' => $this->an_type,
            'laboratory_name' => $this->an_laboratory ?: null,
            'alcohol' => $this->an_alcohol ?: null,
            'residual_sugar' => $this->an_residual_sugar ?: null,
            'total_acidity' => $this->an_total_acidity ?: null,
            'volatile_acidity' => $this->an_volatile_acidity ?: null,
            'ph' => $this->an_ph ?: null,
            'so2_free' => $this->an_so2_free ?: null,
            'so2_total' => $this->an_so2_total ?: null,
            'density' => $this->an_density ?: null,
            'turbidity' => $this->an_turbidity ?: null,
            'color_intensity' => $this->an_color_intensity ?: null,
            'malic_acid' => $this->an_malic_acid ?: null,
            'notes' => $this->an_notes ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->resetAnForm();
        $this->dispatch('close-modal', id: 'modal-analysis');
        $this->toastSuccess(__('Análisis registrado.'));
    }

    public function deleteAnalysis(int $id): void
    {
        WineAnalysis::where('wine_id', $this->wine->id)->findOrFail($id)->delete();
        $this->toastSuccess(__('Análisis eliminado.'));
    }

    private function resetAnForm(): void
    {
        $this->an_container_id = $this->an_oenologist_id = $this->an_laboratory = '';
        $this->an_type = 'own';
        $this->an_alcohol = $this->an_residual_sugar = $this->an_total_acidity = '';
        $this->an_volatile_acidity = $this->an_ph = $this->an_so2_free = '';
        $this->an_so2_total = $this->an_density = $this->an_turbidity = '';
        $this->an_color_intensity = $this->an_malic_acid = $this->an_notes = '';
        $this->an_date = now()->format('Y-m-d');
    }
}
