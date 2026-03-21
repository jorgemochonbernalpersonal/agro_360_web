<?php

namespace App\Livewire\Viticulturist\ContainerReturns;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\PhytosanitaryContainerReturn;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $currentTab              = 'active';
    public string $search                  = '';
    public string $filterCampaign          = '';
    public string $filterCollectionSystem  = '';

    protected $queryString = [
        'currentTab'             => ['as' => 'tab',    'except' => 'active'],
        'search'                 => ['as' => 'q',      'except' => ''],
        'filterCampaign'         => ['as' => 'campaign','except' => ''],
        'filterCollectionSystem' => ['as' => 'system', 'except' => ''],
    ];

    public function mount(): void
    {
        if ($this->filterCampaign === '') {
            $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
            $this->filterCampaign = (string) ($campaign?->id ?? '');
        }
    }

    public function updatingSearch(): void                  { $this->resetPage(); }
    public function updatingFilterCampaign(): void          { $this->resetPage(); }
    public function updatingFilterCollectionSystem(): void  { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'filterCampaign' => '', 'filterCollectionSystem' => ''];
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function archive(int $id): void
    {
        $this->findOwned(PhytosanitaryContainerReturn::class, $id)->update(['active' => false]);
        $this->toastSuccess('Registro archivado.');
    }

    public function unarchive(int $id): void
    {
        $this->findOwned(PhytosanitaryContainerReturn::class, $id)->update(['active' => true]);
        $this->toastSuccess('Registro restaurado.');
    }

    public function delete(int $id): void
    {
        $this->findOwned(PhytosanitaryContainerReturn::class, $id)->delete();
        $this->toastSuccess('Registro eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return PhytosanitaryContainerReturn::where('viticulturist_id', $this->viticulturistId())
            ->where('active', $this->currentTab === 'active');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('product_name', 'like', '%' . $this->search . '%')
                  ->orWhere('collection_point', 'like', '%' . $this->search . '%')
                  ->orWhere('registration_number', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterCollectionSystem) {
            $query->where('collection_system', $this->filterCollectionSystem);
        }
    }

    protected function defaultOrderBy(): array { return ['date', 'desc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        $userId    = $this->viticulturistId();
        $baseQuery = PhytosanitaryContainerReturn::where('viticulturist_id', $userId);

        $stats = [
            'active'            => (clone $baseQuery)->where('active', true)->count(),
            'archived'          => (clone $baseQuery)->where('active', false)->count(),
            'total_containers'  => (clone $baseQuery)->where('active', true)
                ->when($this->filterCampaign, fn($q) => $q->where('campaign_id', $this->filterCampaign))
                ->sum('containers_quantity'),
        ];

        return [
            'entries'           => $entries,
            'campaigns'         => Campaign::forViticulturist($userId)->orderByDesc('year')->get(),
            'collectionSystems' => PhytosanitaryContainerReturn::COLLECTION_SYSTEMS,
            'containerTypes'    => PhytosanitaryContainerReturn::CONTAINER_TYPES,
            'stats'             => $stats,
        ];
    }
}
