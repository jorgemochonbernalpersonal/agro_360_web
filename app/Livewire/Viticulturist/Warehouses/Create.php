<?php

namespace App\Livewire\Viticulturist\Warehouses;

use App\Models\Warehouse;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    use WithToastNotifications;

    public $name = '';
    public $location = '';
    public $description = '';

    public function mount()
    {
        if (!Auth::user()->isViticulturist()) {
            abort(403);
        }
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

        Warehouse::create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'location' => $this->location ?: null,
            'description' => $this->description ?: null,
            'active' => true,
        ]);

        $this->toastSuccess('Almacén creado exitosamente.');
        return redirect()->route('viticulturist.warehouses.index');
    }

    public function render()
    {
        return view('livewire.viticulturist.warehouses.create')
            ->layout('layouts.app', [
                'title' => 'Crear Almacén - Agro365',
            ]);
    }
}
