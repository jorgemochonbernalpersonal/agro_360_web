<?php

namespace App\Livewire\Winery\Clients;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $currentTab  = 'active';
    public string $search      = '';
    public string $filterType  = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search'     => ['except' => ''],
        'filterType' => ['except' => ''],
    ];

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search     = '';
        $this->filterType = '';
        $this->resetPage();
    }

    public function toggleActive(int $clientId): void
    {
        $client = Client::where('user_id', Auth::id())->findOrFail($clientId);
        $newState = !$client->active;
        $client->update(['active' => $newState]);

        if ($newState) {
            $this->toastSuccess('Cliente activado correctamente.');
            if ($this->currentTab === 'inactive') $this->currentTab = 'active';
        } else {
            $this->toastSuccess('Cliente desactivado correctamente.');
            if ($this->currentTab === 'active') $this->currentTab = 'inactive';
        }
    }

    public function render()
    {
        $userId = Auth::id();

        $query = Client::where('user_id', $userId)
            ->with(['addresses.municipality', 'addresses.province', 'invoices']);

        if ($this->filterType) {
            $query->where('client_type', $this->filterType);
        }

        if ($this->currentTab === 'active') {
            $query->where('active', true);
        } elseif ($this->currentTab === 'inactive') {
            $query->where('active', false);
        }

        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                  ->orWhere('last_name', 'like', $term)
                  ->orWhere('company_name', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('phone', 'like', $term)
                  ->orWhere('company_document', 'like', $term)
                  ->orWhere('particular_document', 'like', $term);
            });
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(12);

        $stats = [
            'active'   => Client::where('user_id', $userId)->where('active', true)->count(),
            'inactive' => Client::where('user_id', $userId)->where('active', false)->count(),
        ];

        return view('livewire.winery.clients.index', [
            'clients' => $clients,
            'stats'   => $stats,
        ])->layout('layouts.app');
    }
}
