<?php

namespace App\Livewire\Winery\Oenologists;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Oenologist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $currentTab = 'active';

    public string $search = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search' => ['except' => ''],
    ];

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $oenologist = Oenologist::where('user_id', Auth::id())->findOrFail($id);
        $newState = ! $oenologist->active;
        $oenologist->update(['active' => $newState]);

        if ($newState) {
            $this->toastSuccess(__('Enólogo activado correctamente.'));
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess(__('Enólogo desactivado correctamente.'));
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    public function delete(int $id): void
    {
        $oenologist = Oenologist::where('user_id', Auth::id())->findOrFail($id);
        $oenologist->delete();
        $this->toastSuccess(__('Enólogo eliminado correctamente.'));
    }

    public function render()
    {
        $userId = Auth::id();

        $query = Oenologist::where('user_id', $userId);

        if ($this->currentTab === 'active') {
            $query->where('active', true);
        } elseif ($this->currentTab === 'inactive') {
            $query->where('active', false);
        }

        if ($this->search) {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('surname', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('license_number', 'like', $term);
            });
        }

        $oenologists = $query->orderBy('name')->paginate(12);

        $stats = [
            'active' => Oenologist::where('user_id', $userId)->where('active', true)->count(),
            'inactive' => Oenologist::where('user_id', $userId)->where('active', false)->count(),
        ];

        return view('livewire.winery.oenologists.index', [
            'oenologists' => $oenologists,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}
