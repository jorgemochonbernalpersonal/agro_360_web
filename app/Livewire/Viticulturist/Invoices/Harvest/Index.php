<?php

namespace App\Livewire\Viticulturist\Invoices\Harvest;

use App\Livewire\Concerns\WithListing;
use App\Models\Client;
use App\Models\Harvest;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithListing;

    public $selectedCampaign = '';

    public $yearFilter;

    protected $queryString = [
        'selectedCampaign' => ['except' => ''],
        'yearFilter' => ['as' => 'year'],
    ];

    public function mount()
    {
        $this->yearFilter = $this->yearFilter ?? now()->year;
    }

    public function updatingSelectedCampaign()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Harvest::whereHas('activity', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })
            ->with(['activity.plot', 'plotPlanting.grapeVariety', 'container'])
        // Only exclude harvests in active (non-cancelled) invoices
            ->whereDoesntHave('invoiceItems', function ($q) {
                $q->whereHas('invoice', fn ($q2) => $q2->where('status', '!=', 'cancelled'));
            });

        if ($this->selectedCampaign) {
            $query->whereHas('activity', function ($q) {
                $q->where('campaign_id', $this->selectedCampaign);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('activity.plot', function ($subQ) {
                    $subQ->where('name', 'like', '%'.$this->search.'%');
                })
                    ->orWhereHas('plotPlanting.grapeVariety', function ($subQ) {
                        $subQ->where('name', 'like', '%'.$this->search.'%');
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
        ])->layout('layouts.app', [
            'title' => __('Facturación de Cosechas - Agro365'),
            'description' => __('Factura tus cosechas directamente desde la vendimia. Gestión completa de facturación de uva con análisis de rendimientos y precios.'),
        ]);
    }

    protected function defaultTab(): string
    {
        return 'list';
    }

    private function getAdvancedStatistics($user)
    {
        $year = (int) $this->yearFilter;
        $yearStart = Carbon::create($year, 1, 1)->startOfYear();
        $yearEnd = Carbon::create($year, 1, 1)->endOfYear();

        // Todas las cosechas del año en una sola consulta: invoiceItems se
        // resuelve con withExists() (antes: 1 exists() por cosecha en un
        // bucle, ~N consultas adicionales).
        $yearHarvests = Harvest::whereHas('activity', function ($q) use ($user, $yearStart, $yearEnd) {
            $q->where('viticulturist_id', $user->id)
                ->whereBetween('activity_date', [$yearStart, $yearEnd]);
        })
            ->withExists('invoiceItems')
            ->with(['plotPlanting.grapeVariety:id,name', 'activity.plot:id,name'])
            ->get();

        // Total cosechado este año
        $totalHarvested = (float) $yearHarvests->sum('total_weight');

        // Total facturado de cosechas
        $totalInvoiced = (float) $yearHarvests->where('invoice_items_exists', true)->sum('total_weight');

        // Pendiente de facturar
        $pendingToInvoice = $totalHarvested - $totalInvoiced;

        // Porcentaje facturado
        $invoicedPercentage = $totalHarvested > 0 ? ($totalInvoiced / $totalHarvested) * 100 : 0;

        // Por variedad
        $byVariety = $yearHarvests
            ->groupBy(fn ($h) => $h->plotPlanting?->grapeVariety?->name ?? 'Sin variedad')
            ->map(function ($harvests, $variety) {
                $total = (float) $harvests->sum('total_weight');
                $invoiced = (float) $harvests->where('invoice_items_exists', true)->sum('total_weight');
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
        $harvestRevenue = Invoice::whereHas('items.harvest.activity', function ($q) use ($user, $yearStart, $yearEnd) {
            $q->where('viticulturist_id', $user->id)
                ->whereBetween('activity_date', [$yearStart, $yearEnd]);
        })->sum('total_amount');

        // Precio medio por kg
        $avgPricePerKg = $totalInvoiced > 0 ? $harvestRevenue / $totalInvoiced : 0;

        // Cosechas por mes (últimos 12 meses) — una sola consulta agrupada por
        // año/mes en vez de 12 consultas (una por mes) en un bucle.
        $twelveMonthsStart = now()->subMonths(11)->startOfMonth();
        $monthlyRows = Harvest::query()
            ->join('agricultural_activities', 'agricultural_activities.id', '=', 'harvests.activity_id')
            ->where('agricultural_activities.viticulturist_id', $user->id)
            ->where('agricultural_activities.activity_date', '>=', $twelveMonthsStart)
            ->selectRaw('YEAR(agricultural_activities.activity_date) as y, MONTH(agricultural_activities.activity_date) as m, COALESCE(SUM(harvests.total_weight), 0) as weight')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn ($row) => $row->y.'-'.$row->m);

        $harvestsByMonth = collect(range(11, 0))->map(function ($monthsAgo) use ($monthlyRows) {
            $date = now()->subMonths($monthsAgo);
            $row = $monthlyRows->get($date->year.'-'.$date->month);

            return [
                'month' => $date->format('M'),
                'weight' => $row ? (float) $row->weight : 0.0,
            ];
        });

        // Top parcelas por rendimiento
        $topPlots = $yearHarvests
            ->groupBy(fn ($h) => $h->activity?->plot?->name ?? 'Sin parcela')
            ->map(function ($harvests, $plotName) {
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
