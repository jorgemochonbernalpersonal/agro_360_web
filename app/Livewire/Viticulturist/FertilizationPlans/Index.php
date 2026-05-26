<?php

namespace App\Livewire\Viticulturist\FertilizationPlans;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\FertilizationPlan;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search         = '';
    public string $filterCampaign = '';
    public string $filterStatus   = '';

    protected $queryString = [
        'search'         => ['as' => 'q',       'except' => ''],
        'filterCampaign' => ['as' => 'campaign', 'except' => ''],
        'filterStatus'   => ['as' => 'status',  'except' => ''],
    ];

    public function mount(): void
    {
        if ($this->filterCampaign === '') {
            $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
            $this->filterCampaign = (string) ($campaign?->id ?? '');
        }
    }

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingFilterCampaign(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void   { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'filterCampaign' => '', 'filterStatus' => ''];
    }

    public function activate(int $id): void
    {
        $this->findOwned(FertilizationPlan::class, $id)->update(['status' => 'active']);
        $this->toastSuccess(__('Plan activado.'));
    }

    public function archive(int $id): void
    {
        $this->findOwned(FertilizationPlan::class, $id)->update(['status' => 'archived']);
        $this->toastSuccess(__('Plan archivado.'));
    }

    public function delete(int $id): void
    {
        $record = $this->findOwned(FertilizationPlan::class, $id);
        if (!$record->isDraft()) {
            $this->toastError(__('Solo se pueden eliminar planes en borrador.'));
            return;
        }
        $record->delete();
        $this->toastSuccess(__('Plan eliminado.'));
    }

    protected function baseQuery(): Builder
    {
        return FertilizationPlan::where('viticulturist_id', $this->viticulturistId())
            ->where('active', true);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('prepared_by', 'like', '%' . $this->search . '%')
                  ->orWhere('plan_year', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
    }

    protected function defaultOrderBy(): array { return ['plan_year', 'desc']; }
    protected function perPage(): int          { return 15; }

    protected function viewData(mixed $entries): array
    {
        $userId    = $this->viticulturistId();
        $baseQuery = FertilizationPlan::where('viticulturist_id', $userId)->where('active', true);

        $stats = [
            'draft'        => (clone $baseQuery)->where('status', 'draft')->count(),
            'active'       => (clone $baseQuery)->where('status', 'active')->count(),
            'archived'     => (clone $baseQuery)->where('status', 'archived')->count(),
            'nitrate_zone' => (clone $baseQuery)->where('nitrate_zone', true)->count(),
        ];

        return [
            'entries'   => $entries,
            'campaigns' => Campaign::forViticulturist($userId)->orderByDesc('year')->get(),
            'statuses'  => FertilizationPlan::STATUSES,
            'stats'     => $stats,
        ];
    }
}
