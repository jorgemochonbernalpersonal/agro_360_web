<?php

namespace App\Livewire\Winery\Cellar\ContainerRooms;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\ContainerRoom;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $room = ContainerRoom::where('user_id', Auth::id())->findOrFail($id);

        if ($room->containers()->exists()) {
            $this->toastError(__('No se puede eliminar una sala que tiene depósitos asignados.'));

            return;
        }

        $room->delete();
        $this->toastSuccess(__('Sala eliminada correctamente.'));
    }

    public function render()
    {
        $query = ContainerRoom::where('user_id', Auth::id())
            ->when($this->search, function ($q) {
                $term = '%'.mb_strtolower($this->search).'%';
                $q->whereRaw('LOWER(name) LIKE ?', [$term]);
            })
            ->withCount('containers')
            ->orderBy('name');

        return view('livewire.winery.cellar.container-rooms.index', [
            'rooms' => $query->paginate(20),
        ])->layout('layouts.app');
    }
}
