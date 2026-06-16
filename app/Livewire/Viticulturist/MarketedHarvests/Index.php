<?php

namespace App\Livewire\Viticulturist\MarketedHarvests;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\MarketedHarvest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Index extends AbstractIndex
{
    use WithRoleAwareRedirect;

    public string $filterCampaign = '';

    public string $filterDestination = '';

    public function mount(): void
    {
        $campaign = Campaign::getOrCreateActiveForYear(Auth::id());
        $this->filterCampaign = (string) ($campaign->id ?? '');
    }

    public function updatingFilterCampaign(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDestination(): void
    {
        $this->resetPage();
    }

    public function generateInvoice(int $id): void
    {
        $entry = $this->findOwned(MarketedHarvest::class, $id);

        $this->viticulturistRoleRedirect('invoices.create', [
            'harvest_id' => $entry->harvest_id,
            'marketed_harvest_id' => $entry->id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->findOwned(MarketedHarvest::class, $id)->delete();
        $this->toastSuccess(__('Entrega eliminada.'));
    }

    protected function filterDefaults(): array
    {
        return ['filterCampaign' => '', 'filterDestination' => ''];
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

    protected function defaultOrderBy(): array
    {
        return ['delivery_date', 'desc'];
    }

    protected function perPage(): int
    {
        return 15;
    }

    protected function viewData(mixed $entries): array
    {
        $base = MarketedHarvest::where('viticulturist_id', $this->viticulturistId())->active();

        $stats = [
            'total' => (clone $base)->count(),
            'this_campaign' => $this->filterCampaign ? (clone $base)->where('campaign_id', $this->filterCampaign)->count() : (clone $base)->count(),
            'total_kg' => (clone $base)->sum('quantity_kg'),
            'invoiced' => (clone $base)->whereNotNull('invoice_id')->count(),
        ];

        return [
            'entries' => $entries,
            'campaigns' => Campaign::forViticulturist($this->viticulturistId())->orderByDesc('year')->get(),
            'destinations' => MarketedHarvest::DESTINATION_TYPES,
            'stats' => $stats,
        ];
    }
}
