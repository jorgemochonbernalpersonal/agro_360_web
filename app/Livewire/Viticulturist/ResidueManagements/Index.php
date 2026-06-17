<?php

namespace App\Livewire\Viticulturist\ResidueManagements;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\ResidueManagement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Index extends AbstractIndex
{
    public string $filterCampaign = '';

    public string $filterPractice = '';

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());
        $this->filterCampaign = (string) ($campaign->id ?? '');
    }

    public function updatingFilterCampaign(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPractice(): void
    {
        $this->resetPage();
    }

    public function deactivate(int $id): void
    {
        $this->findOwned(ResidueManagement::class, $id)->update(['active' => false]);
        $this->toastSuccess(__('Registro archivado.'));
    }

    protected function filterDefaults(): array
    {
        return ['filterCampaign' => '', 'filterPractice' => ''];
    }

    protected function baseQuery(): Builder
    {
        return ResidueManagement::with(['plot', 'plotPlanting.grapeVariety'])
            ->where('viticulturist_id', $this->viticulturistId())
            ->active();
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterPractice) {
            $query->where('practice_type', $this->filterPractice);
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
        $base = ResidueManagement::where('viticulturist_id', $this->viticulturistId())->active();

        $stats = [
            'total' => (clone $base)->count(),
            'this_campaign' => $this->filterCampaign ? (clone $base)->where('campaign_id', $this->filterCampaign)->count() : (clone $base)->count(),
            'composted' => (clone $base)->where('practice_type', 'composting')->count(),
            'removed' => (clone $base)->whereIn('practice_type', ['contractor', 'winery_delivery'])->count(),
        ];

        return [
            'entries' => $entries,
            'campaigns' => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'practiceTypes' => ResidueManagement::practiceTypeOptions(),
            'stats' => $stats,
        ];
    }
}
