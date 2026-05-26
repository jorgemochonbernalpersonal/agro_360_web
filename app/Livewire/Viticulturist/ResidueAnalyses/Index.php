<?php

namespace App\Livewire\Viticulturist\ResidueAnalyses;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\ResidueAnalysis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Index extends AbstractIndex
{
    public string $filterCampaign  = '';
    public string $filterCompliant = '';

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());
        $this->filterCampaign = (string) ($campaign?->id ?? '');
    }

    public function updatingFilterCampaign(): void  { $this->resetPage(); }
    public function updatingFilterCompliant(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['filterCampaign' => '', 'filterCompliant' => ''];
    }

    public function deactivate(int $id): void
    {
        $this->findOwned(ResidueAnalysis::class, $id)->update(['active' => false]);
        $this->toastSuccess(__('Análisis archivado.'));
    }

    protected function baseQuery(): Builder
    {
        return ResidueAnalysis::with(['plotPlanting.plot', 'plotPlanting.grapeVariety'])
            ->where('viticulturist_id', $this->viticulturistId())
            ->active();
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterCompliant !== '') {
            $query->where('overall_compliant', (bool) $this->filterCompliant);
        }
    }

    protected function defaultOrderBy(): array { return ['analysis_date', 'desc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        $base = ResidueAnalysis::where('viticulturist_id', $this->viticulturistId())->active();

        $stats = [
            'total'     => (clone $base)->count(),
            'compliant' => (clone $base)->where('overall_compliant', true)->count(),
            'failed'    => (clone $base)->where('overall_compliant', false)->count(),
        ];

        return [
            'entries'   => $entries,
            'campaigns' => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'stats'     => $stats,
        ];
    }
}
