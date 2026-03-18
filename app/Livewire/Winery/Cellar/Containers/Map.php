<?php

namespace App\Livewire\Winery\Cellar\Containers;

use App\Models\Container;
use App\Models\ContainerRoom;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Map extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $roomFilter = '';

    #[Url]
    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetPage(): void {}

    public function render()
    {
        $userId = Auth::id();

        $query = Container::where('user_id', $userId)
            ->where('archived', false)
            ->with(['containerType', 'containerRoom', 'currentStates' => fn($q) => $q->latest('last_movement_at')->with('wine')])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('serial_number', 'like', "%{$this->search}%"))
            ->when($this->roomFilter, fn($q) => $q->where('container_room_id', $this->roomFilter))
            ->orderBy('container_room_id')
            ->orderBy('name');

        match ($this->statusFilter) {
            'full'   => $query->whereRaw('capacity > 0 AND used_capacity / capacity >= 0.9'),
            'empty'  => $query->where(fn($q) => $q->whereNull('used_capacity')->orWhere('used_capacity', 0)),
            'in_use' => $query->where('used_capacity', '>', 0)->whereRaw('capacity <= 0 OR used_capacity / capacity < 0.9'),
            default  => null,
        };

        $containers = $query->get();
        $rooms = ContainerRoom::where('user_id', $userId)->orderBy('name')->get();

        $totalCapacity = $containers->sum('capacity') ?: 1;
        $totalUsed     = $containers->sum('used_capacity');

        $stats = [
            'total'          => $containers->count(),
            'full'           => $containers->filter(fn($c) => $c->getOccupancyPercentage() >= 90)->count(),
            'empty'          => $containers->filter(fn($c) => $c->isEmpty())->count(),
            'in_use'         => $containers->filter(fn($c) => !$c->isEmpty() && $c->getOccupancyPercentage() < 90)->count(),
            'total_capacity' => $totalCapacity,
            'total_used'     => $totalUsed,
            'global_pct'     => round(($totalUsed / $totalCapacity) * 100, 1),
        ];

        $grouped = $containers->groupBy(fn($c) => $c->containerRoom?->name ?? 'Sin sala asignada');

        return view('livewire.winery.cellar.containers.map', compact('grouped', 'rooms', 'stats'));
    }
}
