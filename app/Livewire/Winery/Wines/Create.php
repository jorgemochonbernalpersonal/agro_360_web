<?php

namespace App\Livewire\Winery\Wines;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Wine;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $name          = '';
    public string $vintage       = '';
    public string $wine_type     = 'red';
    public string $status        = 'in_progress';
    public string $variety       = '';
    public string $volume_liters = '';
    public string $internal_code = '';
    public string $notes         = '';

    protected function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:200'],
            'vintage'       => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'wine_type'     => ['required', 'in:' . implode(',', array_keys(Wine::WINE_TYPES))],
            'status'        => ['required', 'in:' . implode(',', array_keys(Wine::STATUSES))],
            'variety'       => ['nullable', 'string', 'max:300'],
            'volume_liters' => ['nullable', 'numeric', 'min:0'],
            'internal_code' => ['nullable', 'string', 'max:100'],
            'notes'         => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $wine = Wine::create([
            'user_id'       => Auth::id(),
            'name'          => $this->name,
            'vintage'       => $this->vintage ?: null,
            'wine_type'     => $this->wine_type,
            'status'        => $this->status,
            'variety'       => $this->variety ?: null,
            'volume_liters' => $this->volume_liters ?: null,
            'internal_code' => $this->internal_code ?: null,
            'notes'         => $this->notes ?: null,
        ]);

        $this->toastSuccess("Vino «{$wine->name}» creado correctamente.");
        $this->redirect(route('winery.wines.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.wines.create', [
            'types'    => Wine::WINE_TYPES,
            'statuses' => Wine::STATUSES,
        ])->layout('layouts.app');
    }
}
