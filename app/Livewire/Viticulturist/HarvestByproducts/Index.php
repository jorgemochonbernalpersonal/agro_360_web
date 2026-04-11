<?php

namespace App\Livewire\Viticulturist\HarvestByproducts;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\HarvestByproduct;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $currentTab             = 'active';
    public string $search                 = '';
    public string $filterCampaign         = '';
    public string $filterByproductType    = '';

    protected $queryString = [
        'currentTab'          => ['as' => 'tab',  'except' => 'active'],
        'search'              => ['as' => 'q',    'except' => ''],
        'filterCampaign'      => ['as' => 'campaign', 'except' => ''],
        'filterByproductType' => ['as' => 'type', 'except' => ''],
    ];

    public function mount(): void
    {
        if ($this->filterCampaign === '') {
            $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
            $this->filterCampaign = (string) ($campaign?->id ?? '');
        }
    }

    public function updatingSearch(): void             { $this->resetPage(); }
    public function updatingFilterCampaign(): void     { $this->resetPage(); }
    public function updatingFilterByproductType(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'filterCampaign' => '', 'filterByproductType' => ''];
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function archive(int $id): void
    {
        $this->findOwned(HarvestByproduct::class, $id)->update(['active' => false]);
        $this->toastSuccess('Registro archivado.');
    }

    public function unarchive(int $id): void
    {
        $this->findOwned(HarvestByproduct::class, $id)->update(['active' => true]);
        $this->toastSuccess('Registro restaurado.');
    }

    public function delete(int $id): void
    {
        $this->findOwned(HarvestByproduct::class, $id)->delete();
        $this->toastSuccess('Registro eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return HarvestByproduct::where('viticulturist_id', $this->viticulturistId())
            ->where('active', $this->currentTab === 'active');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('destination_name', 'like', '%' . $this->search . '%')
                  ->orWhere('document_reference', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterByproductType) {
            $query->where('byproduct_type', $this->filterByproductType);
        }
    }

    protected function defaultOrderBy(): array { return ['date', 'desc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        $userId    = $this->viticulturistId();
        $baseQuery = HarvestByproduct::where('viticulturist_id', $userId);

        $activeQ = (clone $baseQuery)->where('active', true)
            ->when($this->filterCampaign, fn($q) => $q->where('campaign_id', $this->filterCampaign));

        $stats = [
            'active'     => (clone $baseQuery)->where('active', true)->count(),
            'archived'   => (clone $baseQuery)->where('active', false)->count(),
            'total_kg'   => $activeQ->sum('quantity_kg'),
            'pomace_kg'  => (clone $activeQ)->where('byproduct_type', 'pomace')->sum('quantity_kg'),
            'stem_kg'    => (clone $activeQ)->where('byproduct_type', 'stem')->sum('quantity_kg'),
            'lees_kg'    => (clone $activeQ)->where('byproduct_type', 'lees')->sum('quantity_kg'),
        ];

        return [
            'entries'          => $entries,
            'campaigns'        => Campaign::forViticulturist($userId)->orderByDesc('year')->get(),
            'byproductTypes'   => HarvestByproduct::BYPRODUCT_TYPES,
            'destinationTypes' => HarvestByproduct::DESTINATION_TYPES,
            'stats'            => $stats,
        ];
    }
}
