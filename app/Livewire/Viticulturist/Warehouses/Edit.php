<?php

namespace App\Livewire\Viticulturist\Warehouses;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Warehouse;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    use WithToastNotifications;

    public Warehouse $warehouse;

    public string $name        = '';
    public string $location    = '';
    public string $description = '';
    public bool   $active      = true;

    public function mount(Warehouse $warehouse): void
    {
        if ($warehouse->user_id !== Auth::id()) {
            abort(403);
        }

        $this->warehouse   = $warehouse;
        $this->name        = $warehouse->name;
        $this->location    = $warehouse->location ?? '';
        $this->description = $warehouse->description ?? '';
        $this->active      = $warehouse->active;
    }

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active'      => 'boolean',
        ];
    }

    public function save(): mixed
    {
        $this->validate();

        $this->warehouse->update([
            'name'        => $this->name,
            'location'    => $this->location ?: null,
            'description' => $this->description ?: null,
            'active'      => $this->active,
        ]);

        $this->toastSuccess('Almacén actualizado correctamente.');

        return $this->redirect(route('viticulturist.almacen.index', ['tab' => 'almacenes']), navigate: true);
    }

    public function render()
    {
        return view('livewire.viticulturist.warehouses.edit')
            ->layout('layouts.app', ['title' => 'Editar Almacén - Agro365']);
    }
}
