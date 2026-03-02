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

    public function updatingFilterCampaign(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['filterCampaign' => ''];
    }

    public function delete(int $id): void
    {
        $this->findOwned(PlotEnvironment::class, $id)->delete();
        $this->toastSuccess('Registro eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return PlotEnvironment::with(['plot', 'plotPlanting.grape'])
            ->where('viticulturist_id', $this->viticulturistId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
    }

    protected function defaultOrderBy(): array { return ['plot_id', 'asc']; }

    protected function viewData(mixed $entries): array
    {
        return [
            'entries'   => $entries,
            'campaigns' => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
        ];
    }
}
