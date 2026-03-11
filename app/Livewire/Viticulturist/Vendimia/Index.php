<?php

namespace App\Livewire\Viticulturist\Vendimia;

use App\Models\Campaign;
use App\Models\EstimatedYield;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\HarvestDelivery;
use App\Models\PlotPlanting;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications;

    public string $currentTab    = 'pending'; // 'delivered' | 'pending'
    public string $search        = '';
    public string $vintageFilter = '';
    public string $statusFilter  = '';

    protected $queryString = [
        'currentTab'    => ['as' => 'tab',    'except' => 'pending'],
        'search'        => ['except' => ''],
        'vintageFilter' => ['except' => ''],
        'statusFilter'  => ['except' => ''],
    ];

    public function mount(): void
    {
        if (!$this->vintageFilter) {
            $campaign = Campaign::forViticulturist(Auth::id())
                ->orderByDesc('year')
                ->first();
            $this->vintageFilter = (string) ($campaign?->year ?? now()->year);
        }
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
    }

    public function updatingSearch(): void { }

    public function clearFilters(): void
    {
        $this->search       = '';
        $this->statusFilter = '';
    }

    public function render()
    {
        $viticulturistId = Auth::id();
        $vintageYear     = (int) ($this->vintageFilter ?: now()->year);

        $campaignYears = Campaign::forViticulturist($viticulturistId)
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->push(now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        // ── Raw data ──────────────────────────────────────────────────────────

        // Harvest records from cuaderno (primary source)
        $harvestsByPlanting = Harvest::with(['activity', 'plotPlanting'])
            ->whereHas('activity', fn ($q) => $q->where('viticulturist_id', $viticulturistId))
            ->where('vintage', $vintageYear)
            ->where('status', 'active')
            ->get()
            ->groupBy('plot_planting_id');

        // Winery receptions grouped by plot_planting_id
        $batchesByPlanting = GrapeReceptionBatch::with(['winery:id,name'])
            ->where('viticulturist_id', $viticulturistId)
            ->where('vintage_year', $vintageYear)
            ->get()
            ->groupBy('plot_planting_id');

        // Manual deliveries grouped by plot_planting_id
        $deliveriesByPlanting = HarvestDelivery::forViticulturist($viticulturistId)
            ->where('vintage_year', $vintageYear)
            ->get()
            ->groupBy('plot_planting_id');

        // Union of all planting IDs across all sources
        $plantingIds = $harvestsByPlanting->keys()
            ->merge($batchesByPlanting->keys())
            ->merge($deliveriesByPlanting->keys())
            ->filter()
            ->unique();

        // Load plantings with relations
        $plantings = PlotPlanting::with(['grapeVariety', 'plot'])
            ->whereIn('id', $plantingIds)
            ->get()
            ->keyBy('id');

        // Confirmed estimated yields (aforo)
        $estimatedYields = EstimatedYield::whereIn('plot_planting_id', $plantingIds)
            ->whereHas('campaign', fn ($q) => $q->where('year', $vintageYear)->where('viticulturist_id', $viticulturistId))
            ->where('status', 'confirmed')
            ->orderByDesc('estimation_round')
            ->get()
            ->keyBy('plot_planting_id');

        // ── Build rows (one per planting) ─────────────────────────────────────

        $allRows = $plantingIds->map(function ($plantingId) use (
            $plantings, $harvestsByPlanting, $batchesByPlanting,
            $deliveriesByPlanting, $estimatedYields, $vintageYear
        ) {
            $planting = $plantings->get($plantingId);

            // Harvest totals from cuaderno
            $plantingHarvests  = $harvestsByPlanting->get($plantingId, collect());
            $harvestKg         = (float) $plantingHarvests->sum('total_weight');
            $harvestCount      = $plantingHarvests->count();
            $lastHarvestDate   = $plantingHarvests->max(fn ($h) => $h->activity?->activity_date);

            // Winery receptions
            $plantingBatches   = $batchesByPlanting->get($plantingId, collect());
            $receivedKg        = (float) $plantingBatches->sum('total_weight_kg');

            // Manual deliveries (disqualified and already-linked ones excluded from totals
            // to avoid double-counting with winery batches)
            $plantingDeliveries = $deliveriesByPlanting->get($plantingId, collect());
            $manualKg           = (float) $plantingDeliveries
                ->where('disqualified', false)
                ->filter(fn ($d) => $d->harvest_id === null)
                ->sum('delivered_kg');

            $totalDeliveredKg  = $receivedKg + $manualKg;
            $hasDelivery       = $totalDeliveredKg > 0;

            // Cupo PAC (límite regulatorio de la plantación)
            $cupoKg       = $planting?->effectiveHarvestLimitKg($vintageYear);
            $cupoPct      = ($cupoKg && $cupoKg > 0 && $totalDeliveredKg > 0)
                ? round($totalDeliveredKg / $cupoKg * 100, 1)
                : null;
            $cupoExceeded = $cupoPct !== null && $cupoPct > 100;

            // Estimated yield (aforo)
            $estimatedYield    = $estimatedYields->get($plantingId);
            $estimatedKg       = $estimatedYield ? (float) $estimatedYield->estimated_total_yield : null;

            // Discrepancy between cuaderno and total delivered
            $discrepancyKg     = null;
            $discrepancyPct    = null;
            if ($harvestKg > 0 && $totalDeliveredKg > 0) {
                $discrepancyKg  = round(abs($harvestKg - $totalDeliveredKg), 1);
                $discrepancyPct = round($discrepancyKg / $harvestKg * 100, 1);
            }

            $status = match (true) {
                $harvestKg === 0.0 && !$hasDelivery                         => 'pending',
                $harvestKg > 0     && !$hasDelivery                         => 'not_delivered',
                $harvestKg === 0.0 && $hasDelivery                          => 'delivery_only',
                $discrepancyPct !== null && $discrepancyPct > 5             => 'discrepancy',
                default                                                      => 'ok',
            };

            return [
                'key'               => 'planting_' . $plantingId,
                'planting_id'       => $plantingId,
                'planting'          => $planting,
                'variety'           => $planting?->grapeVariety?->name ?? $planting?->name ?? '—',
                'plot'              => $planting?->plot?->name ?? '—',
                'area'              => $planting?->area_planted ? (float) $planting->area_planted : null,
                'harvest_kg'        => $harvestKg,
                'harvest_count'     => $harvestCount,
                'last_harvest_date' => $lastHarvestDate,
                'estimated_kg'      => $estimatedKg,
                'winery_batches'    => $plantingBatches,
                'manual_deliveries' => $plantingDeliveries,
                'received_kg'       => $receivedKg,
                'manual_kg'         => $manualKg,
                'total_delivered_kg' => $totalDeliveredKg,
                'has_delivery'      => $hasDelivery,
                'discrepancy_kg'    => $discrepancyKg,
                'discrepancy_pct'   => $discrepancyPct,
                'status'            => $status,
                'cupo_kg'           => $cupoKg,
                'cupo_pct'          => $cupoPct,
                'cupo_exceeded'     => $cupoExceeded,
            ];
        })
        ->filter()
        ->values();

        // ── Tab counts ────────────────────────────────────────────────────────

        $stats = [
            'delivered' => $allRows->where('has_delivery', true)->count(),
            'pending'   => $allRows->where('has_delivery', false)->count(),
        ];

        // ── Apply tab filter ──────────────────────────────────────────────────

        $rows = $allRows->filter(fn ($row) =>
            $this->currentTab === 'delivered'
                ? $row['has_delivery']
                : !$row['has_delivery']
        );

        // ── Apply search ──────────────────────────────────────────────────────

        if ($this->search) {
            $s = strtolower($this->search);
            $rows = $rows->filter(function ($row) use ($s) {
                if (str_contains(strtolower($row['variety']), $s)) return true;
                if (str_contains(strtolower($row['plot']), $s)) return true;
                if ($row['winery_batches']->contains(fn ($b) => str_contains(strtolower($b->winery?->name ?? ''), $s))) return true;
                if ($row['manual_deliveries']->contains(fn ($d) => str_contains(strtolower($d->buyer_name), $s))) return true;
                return false;
            });
        }

        // ── Apply status filter ───────────────────────────────────────────────

        if ($this->statusFilter) {
            $rows = match ($this->statusFilter) {
                'has_dispute' => $rows->filter(fn ($row) =>
                    $row['manual_deliveries']->contains(fn ($d) =>
                        $d->status === 'disputed' && !$d->disqualified
                    )
                ),
                'has_resolved' => $rows->filter(fn ($row) =>
                    $row['manual_deliveries']->contains(fn ($d) =>
                        $d->status === 'resolved' && !$d->disqualified
                    )
                ),
                default => $rows->filter(fn ($row) => $row['status'] === $this->statusFilter),
            };
        }

        $rows = $rows->values();

        return view('livewire.viticulturist.vendimia.index', [
            'rows'          => $rows,
            'stats'         => $stats,
            'campaignYears' => $campaignYears,
            'vintageYear'   => $vintageYear,
        ])->layout('layouts.app', [
            'title'       => 'Mis cosechas - Agro365',
            'description' => 'Gestiona tus cosechas y entregas de uva.',
        ]);
    }
}
