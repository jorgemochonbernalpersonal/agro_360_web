<?php

namespace App\Livewire\Viticulturist\Invoices\Harvest;

use App\Models\Harvest;
use App\Models\Client;
use App\Models\Invoice;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $currentTab = 'list';
    public $search = '';
    public $selectedCampaign = '';
    public $yearFilter;

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'list'],
        'search' => ['except' => ''],
        'selectedCampaign' => ['except' => ''],
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

    public function updatingSelectedCampaign()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Harvest::whereHas('activity', function($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
        ->with(['activity.plot', 'plotPlanting.grapeVariety', 'container'])
        ->whereDoesntHave('invoiceItems'); // Solo cosechas sin facturar

        if ($this->selectedCampaign) {
            $query->whereHas('activity', function($q) {
                $q->where('campaign_id', $this->selectedCampaign);
            });
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('activity.plot', function($subQ) {
                    $subQ->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('plotPlanting.grapeVariety', function($subQ) {
                    $subQ->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        $harvests = $query->orderBy('harvest_start_date', 'desc')->paginate(15);
        $availableClients = Client::forUser($user->id)->active()->get();

        // Estadísticas avanzadas (solo para tab de estadísticas)
        $advancedStats = [];
        if ($this->currentTab === 'statistics') {
            $advancedStats = $this->getAdvancedStatistics($user);
        }

        return view('livewire.viticulturist.invoices.harvest.index', [
            'harvests' => $harvests,
            'availableClients' => $availableClients,
            'advancedStats' => $advancedStats,
        ])->layout('layouts.app');
    }

    private function getAdvancedStatistics($user)
    {
        $year = $this->yearFilter;
        
        // Total cosechado este año
        $totalHarvested = Harvest::whereHas('activity', function($q) use ($user, $year) {
            $q->where('viticulturist_id', $user->id)
              ->whereYear('activity_date', $year);
        })->sum('total_weight') ?? 0;

        // Total facturado de cosechas
        $totalInvoiced = Harvest::whereHas('activity', function($q) use ($user, $year) {
            $q->where('viticulturist_id', $user->id)
              ->whereYear('activity_date', $year);
        })
        ->whereHas('invoiceItems')
        ->sum('total_weight') ?? 0;

        // Pendiente de facturar
        $pendingToInvoice = $totalHarvested - $totalInvoiced;

        // Porcentaje facturado
        $invoicedPercentage = $totalHarvested > 0 ? ($totalInvoiced / $totalHarvested) * 100 : 0;

        // Por variedad
        $byVariety = Harvest::whereHas('activity', function($q) use ($user, $year) {
            $q->where('viticulturist_id', $user->id)
              ->whereYear('activity_date', $year);
        })
        ->with('plotPlanting.grapeVariety')
        ->get()
        ->groupBy(fn($h) => $h->plotPlanting?->grapeVariety?->name ?? 'Sin variedad')
        ->map(function($harvests, $variety) {
            $total = $harvests->sum('total_weight');
            $invoiced = $harvests->filter(fn($h) => $h->invoiceItems()->exists())->sum('total_weight');
            $pending = $total - $invoiced;
            
            return [
                'variety' => $variety,
                'total' => $total,
                'invoiced' => $invoiced,
                'pending' => $pending,
                'percentage' => $total > 0 ? ($invoiced / $total) * 100 : 0,
            ];
        })
        ->sortByDesc('total')
        ->take(10);

        // Ingresos por facturación de cosechas
        $harvestRevenue = Invoice::whereHas('items.harvest.activity', function($q) use ($user, $year) {
            $q->where('viticulturist_id', $user->id)
              ->whereYear('activity_date', $year);
        })->sum('total_amount') ?? 0;

        // Precio medio por kg
        $avgPricePerKg = $totalInvoiced > 0 ? $harvestRevenue / $totalInvoiced : 0;

        // Cosechas por mes (últimos 12 meses)
        $harvestsByMonth = collect(range(11, 0))->map(function($monthsAgo) use ($user) {
            $date = now()->subMonths($monthsAgo);
            $weight = Harvest::whereHas('activity', function($q) use ($user, $date) {
                $q->where('viticulturist_id', $user->id)
                  ->whereYear('activity_date', $date->year)
                  ->whereMonth('activity_date', $date->month);
            })->sum('total_weight') ?? 0;
            
            return [
                'month' => $date->format('M'),
                'weight' => $weight,
            ];
        });

        // Top parcelas por rendimiento
        $topPlots = Harvest::whereHas('activity', function($q) use ($user, $year) {
            $q->where('viticulturist_id', $user->id)
              ->whereYear('activity_date', $year);
        })
        ->with('activity.plot')
        ->get()
        ->groupBy(fn($h) => $h->activity?->plot?->name ?? 'Sin parcela')
        ->map(function($harvests, $plotName) {
            return [
                'plot' => $plotName,
                'total_weight' => $harvests->sum('total_weight'),
                'harvests_count' => $harvests->count(),
            ];
        })
        ->sortByDesc('total_weight')
        ->take(10);

        return [
            'totalHarvested' => $totalHarvested,
            'totalInvoiced' => $totalInvoiced,
            'pendingToInvoice' => $pendingToInvoice,
            'invoicedPercentage' => $invoicedPercentage,
            'byVariety' => $byVariety,
            'harvestRevenue' => $harvestRevenue,
            'avgPricePerKg' => $avgPricePerKg,
            'harvestsByMonth' => $harvestsByMonth,
            'topPlots' => $topPlots,
        ];
    }
}
