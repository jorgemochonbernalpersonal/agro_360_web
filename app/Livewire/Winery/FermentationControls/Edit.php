<?php

namespace App\Livewire\Winery\FermentationControls;

use App\Livewire\Winery\AbstractEdit;
use App\Models\Container;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Edit extends AbstractEdit
{
    public WineFermentationControl $control;

    public string $wine_id = '';

    public string $container_id = '';

    public string $control_date = '';

    public string $temperature = '';

    public string $brix_degree = '';

    public string $baume_degree = '';

    public string $density = '';

    public string $ph = '';

    public string $volatile_acidity = '';

    public string $notes = '';

    public function mount(WineFermentationControl $control): void
    {
        $this->authorize('update', $control);

        $this->control = $control;
        $this->wine_id = (string) $control->wine_id;
        $this->container_id = (string) $control->container_id;
        $this->control_date = $control->control_date->format('Y-m-d\TH:i');
        $this->temperature = $control->temperature !== null ? (string) $control->temperature : '';
        $this->brix_degree = $control->brix_degree !== null ? (string) $control->brix_degree : '';
        $this->baume_degree = $control->baume_degree !== null ? (string) $control->baume_degree : '';
        $this->density = $control->density !== null ? (string) $control->density : '';
        $this->ph = $control->ph !== null ? (string) $control->ph : '';
        $this->volatile_acidity = $control->volatile_acidity !== null ? (string) $control->volatile_acidity : '';
        $this->notes = $control->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'wine_id' => ['required', Rule::exists('wines', 'id')->where('user_id', Auth::id())],
            'container_id' => ['required', Rule::exists('containers', 'id')->where('user_id', Auth::id())],
            'control_date' => ['required', 'date'],
            'temperature' => ['nullable', 'numeric', 'min:-20', 'max:60'],
            'brix_degree' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'baume_degree' => ['nullable', 'numeric', 'min:0', 'max:25'],
            'density' => ['nullable', 'numeric', 'min:0.9', 'max:1.3'],
            'ph' => ['nullable', 'numeric', 'min:0', 'max:14'],
            'volatile_acidity' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function performUpdate(): void
    {
        $this->control->update([
            'wine_id' => $this->wine_id,
            'container_id' => $this->container_id,
            'control_date' => $this->control_date,
            'temperature' => $this->temperature !== '' ? $this->temperature : null,
            'brix_degree' => $this->brix_degree !== '' ? $this->brix_degree : null,
            'baume_degree' => $this->baume_degree !== '' ? $this->baume_degree : null,
            'density' => $this->density !== '' ? $this->density : null,
            'ph' => $this->ph !== '' ? $this->ph : null,
            'volatile_acidity' => $this->volatile_acidity !== '' ? $this->volatile_acidity : null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Control actualizado.');
    }

    protected function indexRoute(): string
    {
        return 'winery.fermentation-controls.index';
    }

    protected function viewData(): array
    {
        return [
            'wines' => Wine::where('user_id', Auth::id())->orderBy('name')->get(),
            'containers' => Container::where('user_id', Auth::id())->orderBy('name')->get(),
        ];
    }
}
