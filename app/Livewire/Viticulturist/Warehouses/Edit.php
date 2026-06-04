<?php

namespace App\Livewire\Viticulturist\Warehouses;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public Warehouse $warehouse;

    public string $name = '';

    public string $location = '';

    public string $description = '';

    public bool $active = true;

    public function mount(Warehouse $warehouse): void
    {
        if ($warehouse->user_id !== Auth::id()) {
            abort(403);
        }

        $this->warehouse = $warehouse;
        $this->name = $warehouse->name;
        $this->location = $warehouse->location ?? '';
        $this->description = $warehouse->description ?? '';
        $this->active = $warehouse->active;
    }

    public function save(): mixed
    {
        $this->validate();

        $this->warehouse->update([
            'name' => $this->name,
            'location' => $this->location ?: null,
            'description' => $this->description ?: null,
            'active' => $this->active,
        ]);

        $this->toastSuccess(__('Almacén actualizado correctamente.'));

        return $this->viticulturistRoleRedirect('warehouse.index', ['tab' => 'almacenes']);
    }

    public function render()
    {
        return view('livewire.viticulturist.warehouses.edit')
            ->layout('layouts.app', ['title' => __('Editar Almacén - Agro365')]);
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ];
    }
}
