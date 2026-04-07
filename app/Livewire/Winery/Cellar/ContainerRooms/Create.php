<?php

namespace App\Livewire\Winery\Cellar\ContainerRooms;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\ContainerRoom;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $name        = '';
    public string $description = '';
    public string $capacity    = '';
    public string $temperature = '';
    public string $humidity    = '';

    protected function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'capacity'    => ['nullable', 'integer', 'min:1'],
            'temperature' => ['nullable', 'numeric', 'min:-20', 'max:50'],
            'humidity'    => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        ContainerRoom::create([
            'user_id'     => Auth::id(),
            'name'        => $this->name,
            'description' => $this->description ?: null,
            'capacity'    => $this->capacity ?: null,
            'temperature' => $this->temperature ?: null,
            'humidity'    => $this->humidity ?: null,
        ]);

        $this->toastSuccess('Sala de bodega creada correctamente.');
        $this->redirect(roleRoute('container-rooms.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.cellar.container-rooms.create')
            ->layout('layouts.app');
    }
}
