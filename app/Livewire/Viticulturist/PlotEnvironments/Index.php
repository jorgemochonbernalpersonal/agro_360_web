<?php

namespace App\Livewire\Viticulturist\PlotEnvironments;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\PlotEnvironment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Index extends AbstractIndex
{
    public string $filterCampaign = '';

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());
        $this->filterCampaign = (string) ($campaign?->id ?? '');
    }

    public function updatingFilterCampaign(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->findOwned(PlotEnvironment::class, $id)->delete();
        $this->toastSuccess(__('Registro eliminado.'));
    }

    protected function filterDefaults(): array
    {
        return ['filterCampaign' => ''];
    }

    protected function baseQuery(): Builder
    {
        return PlotEnvironment::with(['plot', 'plotPlanting.grapeVariety'])
            ->where('viticulturist_id', $this->viticulturistId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['plot_id', 'asc'];
    }

    protected function viewData(mixed $entries): array
    {
        $base = PlotEnvironment::where('viticulturist_id', $this->viticulturistId());

        $stats = [
            'total' => (clone $base)->count(),
            'water' => (clone $base)->where('water_intake_nearby', true)->count(),
            'protected' => (clone $base)->where(fn ($q) => $q->where('protected_zone_total', true)->orWhere('protected_zone_partial', true))->count(),
            'erosion' => (clone $base)->where('erosion_risk', true)->count(),
        ];

        return [
            'entries' => $entries,
            'campaigns' => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'stats' => $stats,
        ];
    }
}
