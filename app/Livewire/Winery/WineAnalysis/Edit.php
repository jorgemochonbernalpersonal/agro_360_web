<?php

namespace App\Livewire\Winery\WineAnalysis;

use App\Livewire\Winery\AbstractEdit;
use App\Models\Wine;
use App\Models\WineAnalysis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Edit extends AbstractEdit
{
    public WineAnalysis $analysis;

    public string $wine_id = '';

    public string $analysis_type = '';

    public string $analysis_date = '';

    public string $laboratory = '';

    public string $sample_reference = '';

    public string $alcoholic_strength = '';

    public string $total_acidity = '';

    public string $volatile_acidity = '';

    public string $residual_sugar = '';

    public string $free_so2 = '';

    public string $total_so2 = '';

    public string $ph = '';

    public string $density = '';

    public string $result = '';

    public string $notes = '';

    public function mount(WineAnalysis $analysis): void
    {
        $this->authorize('update', $analysis);

        $this->analysis = $analysis;
        $this->wine_id = (string) ($analysis->wine_id ?? '');
        $this->analysis_type = $analysis->analysis_type;
        $this->analysis_date = $analysis->analysis_date->toDateString();
        $this->laboratory = $analysis->laboratory ?? '';
        $this->sample_reference = $analysis->sample_reference ?? '';
        $this->alcoholic_strength = $analysis->alcoholic_strength !== null
            ? (string) $analysis->alcoholic_strength
            : '';
        $this->total_acidity = $analysis->total_acidity !== null
            ? (string) $analysis->total_acidity
            : '';
        $this->volatile_acidity = $analysis->volatile_acidity !== null
            ? (string) $analysis->volatile_acidity
            : '';
        $this->residual_sugar = $analysis->residual_sugar !== null
            ? (string) $analysis->residual_sugar
            : '';
        $this->free_so2 = $analysis->free_so2 !== null
            ? (string) $analysis->free_so2
            : '';
        $this->total_so2 = $analysis->total_so2 !== null
            ? (string) $analysis->total_so2
            : '';
        $this->ph = $analysis->ph !== null
            ? (string) $analysis->ph
            : '';
        $this->density = $analysis->density !== null
            ? (string) $analysis->density
            : '';
        $this->result = $analysis->result ?? 'pending';
        $this->notes = $analysis->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'wine_id' => ['nullable', Rule::exists('wines', 'id')->where('user_id', Auth::id())],
            'analysis_type' => ['required', 'in:'.implode(',', array_keys(WineAnalysis::ANALYSIS_TYPES))],
            'analysis_date' => ['required', 'date'],
            'laboratory' => ['nullable', 'string', 'max:200'],
            'sample_reference' => ['nullable', 'string', 'max:100'],
            'alcoholic_strength' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'total_acidity' => ['nullable', 'numeric', 'min:0'],
            'volatile_acidity' => ['nullable', 'numeric', 'min:0'],
            'residual_sugar' => ['nullable', 'numeric', 'min:0'],
            'free_so2' => ['nullable', 'numeric', 'min:0'],
            'total_so2' => ['nullable', 'numeric', 'min:0'],
            'ph' => ['nullable', 'numeric', 'min:0', 'max:14'],
            'density' => ['nullable', 'numeric', 'min:0'],
            'result' => ['required', 'in:'.implode(',', array_keys(WineAnalysis::RESULTS))],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function performUpdate(): void
    {
        $this->analysis->update([
            'wine_id' => $this->wine_id ?: null,
            'analysis_type' => $this->analysis_type,
            'analysis_date' => $this->analysis_date,
            'laboratory' => $this->laboratory ?: null,
            'sample_reference' => $this->sample_reference ?: null,
            'alcoholic_strength' => $this->alcoholic_strength !== '' ? $this->alcoholic_strength : null,
            'total_acidity' => $this->total_acidity !== '' ? $this->total_acidity : null,
            'volatile_acidity' => $this->volatile_acidity !== '' ? $this->volatile_acidity : null,
            'residual_sugar' => $this->residual_sugar !== '' ? $this->residual_sugar : null,
            'free_so2' => $this->free_so2 !== '' ? $this->free_so2 : null,
            'total_so2' => $this->total_so2 !== '' ? $this->total_so2 : null,
            'ph' => $this->ph !== '' ? $this->ph : null,
            'density' => $this->density !== '' ? $this->density : null,
            'result' => $this->result,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Análisis actualizado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.wine-analysis.index';
    }

    protected function viewData(): array
    {
        return [
            'wines' => Wine::where('user_id', Auth::id())->orderBy('name')->get(),
            'types' => WineAnalysis::analysisTypeOptions(),
            'results' => WineAnalysis::resultOptions(),
        ];
    }
}
