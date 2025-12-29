<?php

namespace App\Livewire\Viticulturist\Warehouses;

use App\Models\Warehouse;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    use WithToastNotifications;

    public $warehouseId;
    public $name = '';
    public $location = '';
    public $description = '';
    public $active = true;

    public function mount($warehouse)
    {
        if (!Auth::user()->isViticulturist()) {
            abort(403);
        }

        $warehouseModel = Warehouse::where('user_id', Auth::id())
            ->findOrFail($warehouse);

        $this->warehouseId = $warehouseModel->id;
        $this->name = $warehouseModel->name;
        $this->location = $warehouseModel->location ?? '';
        $this->description = $warehouseModel->description ?? '';
        $this->active = $warehouseModel->active;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'El nombre del almacén es obligatorio',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
        ]);

        $warehouse = Warehouse::where('user_id', Auth::id())
            ->findOrFail($this->warehouseId);

        $warehouse->update([
            'name' => $this->name,
            'location' => $this->location ?: null,
            'description' => $this->description ?: null,
            'active' => $this->active,
        ]);

        $this->toastSuccess('Almacén actualizado exitosamente.');
        return redirect()->route('viticulturist.warehouses.index');
    }

    public function render()
    {
        return view('livewire.viticulturist.warehouses.edit')
            ->layout('layouts.app', [
                'title' => 'Editar Almacén - Agro365',
            ]);
    }
}
