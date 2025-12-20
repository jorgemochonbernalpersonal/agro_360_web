<?php

namespace App\Livewire\Viticulturist\Clients;

use App\Models\Client;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = '';
    public $filterActive = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterActive' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterActive()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Client::forUser($user->id)
            ->with(['addresses', 'invoices']);

        // Filtros
        if ($this->filterType) {
            $query->where('client_type', $this->filterType);
        }

        if ($this->filterActive !== '') {
            $query->where('active', $this->filterActive === '1');
        }

        // Búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('company_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('company_document', 'like', '%' . $this->search . '%')
                  ->orWhere('particular_document', 'like', '%' . $this->search . '%');
            });
        }

        $clients = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        // Estadísticas
        $stats = [
            'total' => Client::forUser($user->id)->count(),
            'active' => Client::forUser($user->id)->active()->count(),
            'individual' => Client::forUser($user->id)->where('client_type', 'individual')->count(),
            'company' => Client::forUser($user->id)->where('client_type', 'company')->count(),
        ];

        return view('livewire.viticulturist.clients.index', [
            'clients' => $clients,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}
