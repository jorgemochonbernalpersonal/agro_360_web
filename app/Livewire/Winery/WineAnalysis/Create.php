<?php

namespace App\Livewire\Winery\WineAnalysis;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Wine;
use App\Models\WineAnalysis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $wine_id = '';

    public string $analysis_type = 'standard';

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

    public string $result = 'pending';

    public string $notes = '';

    public function save(): void
    {
        $this->validate();

        WineAnalysis::create([
            'user_id' => Auth::id(),
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

        $this->toastSuccess(__('Análisis de laboratorio creado correctamente.'));
        $this->redirect(roleRoute('wine-analysis.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.wine-analysis.create', [
            'wines' => Wine::where('user_id', Auth::id())->orderBy('name')->get(),
            'types' => WineAnalysis::analysisTypeOptions(),
            'results' => WineAnalysis::resultOptions(),
        ])->layout('layouts.app');
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
}
