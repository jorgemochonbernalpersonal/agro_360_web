<?php

namespace App\Livewire\Winery\Silicie;

use App\Livewire\Winery\Silicie\Traits\HasSilicieDashboardQueries;
use App\Models\WineStockSnapshot;
use App\Services\Exporters\SilicieCsvExporter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Dashboard extends Component
{
    use HasSilicieDashboardQueries;

    public string $filterVintage = '';

    public string $filterFiscalYear = '';

    public string $currentTab = 'entries';

    public bool $showExportGuide = false;

    public function mount(): void
    {
        $this->filterVintage = (string) now()->year;
        $this->filterFiscalYear = (string) now()->year;
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
    }

    public function takeSnapshot(): void
    {
        $wineryId = Auth::id();
        $today = now()->toDateString();

        $rows = DB::table('container_current_states as ccs')
            ->join('containers as c', 'c.id', '=', 'ccs.container_id')
            ->join('wines as w', 'w.id', '=', 'ccs.wine_id')
            ->where('c.user_id', $wineryId)
            ->where('w.user_id', $wineryId)
            ->where('ccs.current_quantity', '>', 0)
            ->select([
                'w.id as wine_id',
                'w.vintage',
                'w.wine_type',
                'w.is_must',
                DB::raw('SUM(ccs.current_quantity) as total_liters'),
                DB::raw('COUNT(DISTINCT ccs.container_id) as container_count'),
            ])
            ->groupBy('w.id', 'w.vintage', 'w.wine_type', 'w.is_must')
            ->get();

        foreach ($rows as $row) {
            WineStockSnapshot::updateOrCreate(
                [
                    'user_id' => $wineryId,
                    'wine_id' => $row->wine_id,
                    'snapshot_date' => $today,
                ],
                [
                    'quantity_liters' => $row->total_liters,
                    'container_count' => $row->container_count,
                    'vintage' => $row->vintage,
                    'wine_type' => $row->wine_type,
                    'is_must' => (bool) $row->is_must,
                    'created_by' => $wineryId,
                ]
            );
        }

        $this->dispatch('toast', message: __('Instantánea de existencias registrada.'), type: 'success');
    }

    public function exportCsv(): StreamedResponse
    {
        $wineryId = Auth::id();
        $vintage = (int) ($this->filterVintage ?: now()->year);
        $csv = (new SilicieCsvExporter)->export($wineryId, $vintage);
        $filename = 'SILICIE_'.$wineryId.'_'.$vintage.'.csv';

        $this->showExportGuide = true;

        return response()->streamDownload(
            fn () => print ($csv),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function dismissExportGuide(): void
    {
        $this->showExportGuide = false;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $wineryId = Auth::id();
        $vintage = (int) ($this->filterVintage ?: now()->year);

        $stats = $this->buildStats($wineryId, $vintage);

        $vintages = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->select('vintage')->distinct()
            ->orderByDesc('vintage')
            ->pluck('vintage');

        if ($vintages->isEmpty()) {
            $vintages = collect([now()->year]);
        }

        $fiscalYear = (int) ($this->filterFiscalYear ?: now()->year);

        $tabData = match ($this->currentTab) {
            'entries' => $this->queryEntries($wineryId, $vintage),
            'elaboration' => $this->queryElaboration($wineryId, $vintage),
            'inventory' => $this->queryInventory($wineryId),
            'outputs' => $this->queryOutputs($wineryId, $vintage),
            'opening' => $this->buildFiscalYearOpeningBalances($wineryId, $fiscalYear),
            default => [],
        };

        $fiscalYears = $vintages;

        return view('livewire.winery.silicie.dashboard', [
            'stats' => $stats,
            'vintages' => $vintages,
            'vintage' => $vintage,
            'fiscalYears' => $fiscalYears,
            'fiscalYear' => $fiscalYear,
            'tabData' => $tabData,
        ]);
    }
}
