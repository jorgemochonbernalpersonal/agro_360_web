<?php

namespace App\Livewire\Viticulturist\Warehouses;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public string $name = '';

    public string $location = '';

    public string $description = '';

    public function mount(): void
    {
        if (! Auth::user()->hasViticulturistAccess()) {
            abort(403);
        }
    }

    public function save(): mixed
    {
        $this->validate();

        Warehouse::create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'location' => $this->location ?: null,
            'description' => $this->description ?: null,
            'active' => true,
        ]);

        $this->toastSuccess(__('Almacén creado correctamente.'));

        return $this->viticulturistRoleRedirect('warehouse.index', ['tab' => 'almacenes']);
    }

    public function render()
    {
        return view('livewire.viticulturist.warehouses.create')
            ->layout('layouts.app', ['title' => __('Nuevo Almacén - Agro365')]);
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
