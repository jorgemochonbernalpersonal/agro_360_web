<?php

namespace App\Livewire\Viticulturist\EnergyUsages;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\EnergyUsage;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $currentTab = 'active';

    public string $filterCampaign = '';

    public string $filterEnergyType = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab',     'except' => 'active'],
        'filterCampaign' => ['as' => 'campaign', 'except' => ''],
        'filterEnergyType' => ['as' => 'type',     'except' => ''],
    ];

    public function mount(): void
    {
        if ($this->filterCampaign === '') {
            $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
            $this->filterCampaign = (string) ($campaign->id ?? '');
        }
    }

    public function updatingFilterCampaign(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEnergyType(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function archive(int $id): void
    {
        $this->findOwned(EnergyUsage::class, $id)->update(['active' => false]);
        $this->toastSuccess(__('Registro archivado.'));
    }

    public function unarchive(int $id): void
    {
        $this->findOwned(EnergyUsage::class, $id)->update(['active' => true]);
        $this->toastSuccess(__('Registro restaurado.'));
    }

    protected function filterDefaults(): array
    {
        return ['filterCampaign' => '', 'filterEnergyType' => ''];
    }

    protected function baseQuery(): Builder
    {
        return EnergyUsage::where('viticulturist_id', $this->viticulturistId())
            ->where('active', $this->currentTab === 'active');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterEnergyType) {
            $query->where('energy_type', $this->filterEnergyType);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['date', 'desc'];
    }

    protected function perPage(): int
    {
        return 15;
    }

    protected function viewData(mixed $entries): array
    {
        $userId = $this->viticulturistId();
        $baseQuery = EnergyUsage::where('viticulturist_id', $userId);

        $stats = [
            'active' => (clone $baseQuery)->where('active', true)->count(),
            'archived' => (clone $baseQuery)->where('active', false)->count(),
        ];

        $co2Total = $this->filterCampaign
            ? EnergyUsage::forCampaign($this->filterCampaign)
                ->where('viticulturist_id', $userId)
                ->where('active', true)
                ->sum('co2_kg_equivalent')
            : 0;

        return [
            'entries' => $entries,
            'campaigns' => Campaign::forViticulturist($userId)->orderByDesc('year')->get(),
            'energyTypes' => EnergyUsage::energyTypeOptions(),
            'stats' => $stats,
            'co2Total' => $co2Total,
        ];
    }
}
