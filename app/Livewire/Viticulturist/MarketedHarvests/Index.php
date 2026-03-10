<?php

namespace App\Livewire\Viticulturist\MarketedHarvests;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\MarketedHarvest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Index extends AbstractIndex
{
    public string $filterCampaign    = '';
    public string $filterDestination = '';

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());
        $this->filterCampaign = (string) ($campaign?->id ?? '');
    }

    public function updatingFilterCampaign(): void    { $this->resetPage(); }
    public function updatingFilterDestination(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['filterCampaign' => '', 'filterDestination' => ''];
    }

    public function generateInvoice(int $id): void
    {
        $entry = $this->findOwned(MarketedHarvest::class, $id);

        $this->redirect(route('viticulturist.invoices.create', [
            'harvest_id'          => $entry->harvest_id,
            'marketed_harvest_id' => $entry->id,
        ]));
    }

    public function delete(int $id): void
    {
        $this->findOwned(MarketedHarvest::class, $id)->delete();
        $this->toastSuccess('Entrega eliminada.');
    }

    protected function baseQuery(): Builder
    {
        return MarketedHarvest::with(['harvest.plotPlanting.plot', 'harvest.plotPlanting.grapeVariety', 'invoice'])
            ->where('viticulturist_id', $this->viticulturistId())
            ->active();
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterDestination) {
            $query->where('destination_type', $this->filterDestination);
        }
    }

    protected function defaultOrderBy(): array { return ['delivery_date', 'desc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        return [
            'entries'      => $entries,
            'campaigns'    => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'destinations' => MarketedHarvest::DESTINATION_TYPES,
        ];
    }
}
