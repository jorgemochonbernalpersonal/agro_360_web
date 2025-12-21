<?php

namespace App\Livewire\Viticulturist\Clients;

use App\Models\Client;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $currentTab = 'list';
    public $search = '';
    public $filterType = '';
    public $filterActive = '';
    public $yearFilter;

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'list'],
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterActive' => ['except' => ''],
        'yearFilter' => ['as' => 'year'],
    ];

    public function mount()
    {
        $this->yearFilter = $this->yearFilter ?? now()->year;
    }

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

    public function updatingFilterActive()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        // Lista de clientes
        $query = Client::forUser($user->id)
            ->with(['addresses', 'invoices']);

        if ($this->filterType) {
            $query->where('client_type', $this->filterType);
        }

        if ($this->filterActive !== '') {
            $query->where('active', $this->filterActive === '1');
        }

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

        $clients = $query->orderBy('created_at', 'desc')->paginate(15);

        // Estadísticas básicas
        $stats = [
            'total' => Client::forUser($user->id)->count(),
            'active' => Client::forUser($user->id)->active()->count(),
            'individual' => Client::forUser($user->id)->where('client_type', 'individual')->count(),
            'company' => Client::forUser($user->id)->where('client_type', 'company')->count(),
        ];

        // Estadísticas avanzadas (solo para tab de estadísticas)
        $advancedStats = [];
        if ($this->currentTab === 'statistics') {
            $advancedStats = $this->getAdvancedStatistics($user);
        }

        return view('livewire.viticulturist.clients.index', [
            'clients' => $clients,
            'stats' => $stats,
            'advancedStats' => $advancedStats,
        ])->layout('layouts.app', [
            'title' => 'Clientes - Agro365',
            'description' => 'Gestiona tus clientes y analiza tu cartera. Control completo de clientes particulares y empresas.',
        ]);
    }

    private function getAdvancedStatistics($user)
    {
        $year = $this->yearFilter;
        
        // Clientes con facturas este año
        $activeThisYear = Client::forUser($user->id)
            ->whereHas('invoices', function($q) use ($year) {
                $q->whereYear('invoice_date', $year);
            })
            ->count();

        // Clientes inactivos (sin facturas este año)
        $inactiveThisYear = Client::forUser($user->id)->count() - $activeThisYear;

        // Facturación media por cliente
        $avgInvoicePerClient = Client::forUser($user->id)
            ->withSum(['invoices' => fn($q) => $q->whereYear('invoice_date', $year)], 'total_amount')
            ->get()
            ->filter(fn($c) => $c->invoices_sum_total_amount > 0)
            ->avg('invoices_sum_total_amount') ?? 0;

        // Top 10 clientes por facturación
        $topClients = Client::forUser($user->id)
            ->withSum(['invoices' => fn($q) => $q->whereYear('invoice_date', $year)], 'total_amount')
            ->get()
            ->filter(fn($c) => $c->invoices_sum_total_amount > 0)
            ->sortByDesc('invoices_sum_total_amount')
            ->take(10)
            ->map(function($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->company_name ?: $client->first_name . ' ' . $client->last_name,
                    'total' => $client->invoices_sum_total_amount,
                    'type' => $client->client_type,
                ];
            });

        // Distribución por tipo
        $distributionByType = Client::forUser($user->id)
            ->selectRaw('client_type, COUNT(*) as count')
            ->groupBy('client_type')
            ->get()
            ->mapWithKeys(fn($item) => [$item->client_type => $item->count]);

        // Nuevos clientes por mes (últimos 12 meses)
        $newClientsByMonth = collect(range(11, 0))->map(function($monthsAgo) use ($user) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M'),
                'count' => Client::forUser($user->id)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        });

        return [
            'activeThisYear' => $activeThisYear,
            'inactiveThisYear' => $inactiveThisYear,
            'avgInvoicePerClient' => $avgInvoicePerClient,
            'topClients' => $topClients,
            'distributionByType' => $distributionByType,
            'newClientsByMonth' => $newClientsByMonth,
        ];
    }
}
