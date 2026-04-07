<?php

namespace App\Livewire\Clients;

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

    public function delete(int $clientId): void
    {
        $client = Client::where('user_id', Auth::id())->findOrFail($clientId);

        if ($client->invoices()->exists()) {
            $this->toastError('No se puede eliminar un cliente con facturas asociadas.');
            return;
        }

        $client->delete();
        $this->toastSuccess('Cliente eliminado correctamente.');
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
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(first_name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(company_name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(phone) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(company_document) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(particular_document) LIKE ?', [$term]);
            });
        }

        $clients = $query->orderByDesc('created_at')->paginate(12);

        $stats = [
            'total'    => Client::where('user_id', $userId)->count(),
            'active'   => Client::where('user_id', $userId)->where('active', true)->count(),
            'inactive' => Client::where('user_id', $userId)->where('active', false)->count(),
        ];

        return view('livewire.clients.index', [
            'clients' => $clients,
            'stats'   => $stats,
        ])->layout('layouts.app');
    }
}
