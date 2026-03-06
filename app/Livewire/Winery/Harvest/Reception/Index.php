<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\WineryViticulturist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $search          = '';
    public string $campaignFilter  = '';
    public string $viticulturistFilter = '';

    protected $queryString = [
        'search'             => ['except' => ''],
        'campaignFilter'     => ['except' => ''],
        'viticulturistFilter'=> ['except' => ''],
    ];

    public function updatingSearch(): void           { $this->resetPage(); }
    public function updatingCampaignFilter(): void   { $this->resetPage(); }
    public function updatingViticulturistFilter(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search              = '';
        $this->campaignFilter      = '';
        $this->viticulturistFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $wineryId = Auth::id();

        // IDs de los viticultores vinculados a esta bodega
        $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
            ->pluck('viticulturist_id');

        // Campañas propias de la bodega (para el filtro)
        $campaigns = Campaign::forViticulturist($wineryId)
            ->orderBy('year', 'desc')
            ->get();

        // Cosechas en parcelas de los viticultores de esta bodega,
        // cuya actividad pertenezca a una campaña de la bodega
        $wineryCampaignIds = $campaigns->pluck('id');

        $query = Harvest::with([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'activity.viticulturist',
            'activity.campaign',
            'container',
        ])
        ->whereHas('activity', function (Builder $q) use ($viticulturistIds, $wineryCampaignIds) {
            $q->whereIn('viticulturist_id', $viticulturistIds)
              ->whereIn('campaign_id', $wineryCampaignIds);
        })
        ->orderByDesc('harvest_start_date');

        if ($this->campaignFilter) {
            $query->whereHas('activity', fn(Builder $q) =>
                $q->where('campaign_id', $this->campaignFilter)
            );
        }

        if ($this->viticulturistFilter) {
            $query->whereHas('activity', fn(Builder $q) =>
                $q->where('viticulturist_id', $this->viticulturistFilter)
            );
        }

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->whereHas('activity.viticulturist', fn($q2) =>
                    $q2->whereRaw('LOWER(name) LIKE ?', [$term])
                )
                ->orWhereHas('plotPlanting.grapeVariety', fn($q2) =>
                    $q2->whereRaw('LOWER(name) LIKE ?', [$term])
                )
                ->orWhereHas('plotPlanting.plot', fn($q2) =>
                    $q2->whereRaw('LOWER(name) LIKE ?', [$term])
                )
                ->orWhereRaw('LOWER(IFNULL(harvest_ticket_number, \'\')) LIKE ?', [$term]);
            });
        }

        $receptions = $query->paginate(20);

        // Stats globales (sin filtros)
        $baseQuery = Harvest::whereHas('activity', function (Builder $q) use ($viticulturistIds, $wineryCampaignIds) {
            $q->whereIn('viticulturist_id', $viticulturistIds)
              ->whereIn('campaign_id', $wineryCampaignIds);
        });

        if ($this->campaignFilter) {
            $baseQuery->whereHas('activity', fn(Builder $q) =>
                $q->where('campaign_id', $this->campaignFilter)
            );
        }

        $stats = [
            'total_kg'     => (float) $baseQuery->sum('total_weight'),
            'total_entries'=> $baseQuery->count(),
            'avg_baume'    => round((float) $baseQuery->whereNotNull('baume_degree')->avg('baume_degree'), 2),
        ];

        // Viticultores vinculados (para el filtro)
        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->get()
            ->pluck('viticulturist');

        return view('livewire.winery.harvest.reception.index', [
            'receptions'          => $receptions,
            'campaigns'           => $campaigns,
            'linkedViticulturists'=> $linkedViticulturists,
            'stats'               => $stats,
        ])->layout('layouts.app');
    }
}
