<?php

namespace App\Livewire\Winery\FermentationControls;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\Wine;
use App\Models\WineFermentationControl;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $wine_id          = '';
    public string $container_id     = '';
    public string $control_date     = '';
    public string $temperature      = '';
    public string $brix_degree      = '';
    public string $baume_degree     = '';
    public string $density          = '';
    public string $ph               = '';
    public string $volatile_acidity = '';
    public string $notes            = '';

    public function mount(): void
    {
        $this->control_date = now()->format('Y-m-d\TH:i');
    }

    protected function rules(): array
    {
        return [
            'wine_id'          => ['required', 'exists:wines,id'],
            'container_id'     => ['required', 'exists:containers,id'],
            'control_date'     => ['required', 'date'],
            'temperature'      => ['nullable', 'numeric', 'min:-20', 'max:60'],
            'brix_degree'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'baume_degree'     => ['nullable', 'numeric', 'min:0', 'max:25'],
            'density'          => ['nullable', 'numeric', 'min:0.9', 'max:1.3'],
            'ph'               => ['nullable', 'numeric', 'min:0', 'max:14'],
            'volatile_acidity' => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        WineFermentationControl::create([
            'wine_id'          => $this->wine_id,
            'container_id'     => $this->container_id,
            'control_date'     => $this->control_date,
            'temperature'      => $this->temperature !== '' ? $this->temperature : null,
            'brix_degree'      => $this->brix_degree !== '' ? $this->brix_degree : null,
            'baume_degree'     => $this->baume_degree !== '' ? $this->baume_degree : null,
            'density'          => $this->density !== '' ? $this->density : null,
            'ph'               => $this->ph !== '' ? $this->ph : null,
            'volatile_acidity' => $this->volatile_acidity !== '' ? $this->volatile_acidity : null,
            'notes'            => $this->notes ?: null,
            'created_by'       => Auth::id(),
        ]);

        $this->toastSuccess('Control de fermentación registrado.');
        $this->redirect(route('winery.fermentation-controls.index'), navigate: true);
    }

    public function render()
    {
        $containers = $this->wine_id
            ? Container::where('user_id', Auth::id())->orderBy('name')->get()
            : collect();

        return view('livewire.winery.fermentation-controls.create', [
            'wines'      => Wine::where('user_id', Auth::id())->orderBy('name')->get(),
            'containers' => $containers,
        ])->layout('layouts.app');
    }
}
