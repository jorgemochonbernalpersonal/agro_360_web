<?php

namespace App\Livewire\Viticulturist\Clients;

use App\Models\Client;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $currentTab = 'active'; // 'active', 'inactive'
    public $search = '';
    public $filterType = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'active'],
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
    ];

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search     = '';
        $this->filterType = '';
        $this->resetPage();
    }

    public function delete(int $clientId): void
    {
        $client = Client::forUser(Auth::id())->findOrFail($clientId);

        if ($client->invoices()->exists()) {
            $this->toastError('No se puede eliminar un cliente con facturas asociadas.');
            return;
        }

        $client->delete();
        $this->toastSuccess('Cliente eliminado correctamente.');
    }

    public function toggleActive($clientId)
    {
        $user = Auth::user();
        $client = Client::forUser($user->id)->findOrFail($clientId);

        $newActiveState = !$client->active;

        $client->update(['active' => $newActiveState]);

        if ($newActiveState) {
            $this->toastSuccess('Cliente activado exitosamente.');
            if ($this->currentTab === 'inactive') {
                $this->currentTab = 'active';
            }
        } else {
            $this->toastSuccess('Cliente desactivado exitosamente.');
            if ($this->currentTab === 'active') {
                $this->currentTab = 'inactive';
            }
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = Client::forUser($user->id)
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
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('company_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('company_document', 'like', '%' . $this->search . '%')
                  ->orWhere('particular_document', 'like', '%' . $this->search . '%');
            });
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(12);

        $stats = [
            'total'    => Client::forUser($user->id)->count(),
            'active'   => Client::forUser($user->id)->where('active', true)->count(),
            'inactive' => Client::forUser($user->id)->where('active', false)->count(),
        ];

        return view('livewire.viticulturist.clients.index', [
            'clients' => $clients,
            'stats'   => $stats,
        ])->layout('layouts.app', [
            'title'       => 'Clientes - Agro365',
            'description' => 'Gestiona tus clientes y analiza tu cartera. Control completo de clientes particulares y empresas.',
        ]);
    }
}
