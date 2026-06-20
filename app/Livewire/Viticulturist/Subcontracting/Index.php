<?php

namespace App\Livewire\Viticulturist\Subcontracting;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\Subcontracting;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $filter_campaign_id = '';

    public string $filter_plot_id = '';

    public string $filter_service_type = '';

    public string $filter_invoiced = '';

    protected $queryString = [
        'filter_campaign_id' => ['except' => '', 'as' => 'campaign'],
        'filter_plot_id' => ['except' => '', 'as' => 'plot'],
        'filter_service_type' => ['except' => '', 'as' => 'type'],
        'filter_invoiced' => ['except' => '', 'as' => 'invoiced'],
    ];

    public function updatingFilterCampaignId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPlotId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterServiceType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterInvoiced(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->findOwned(Subcontracting::class, $id)->delete();
        $this->toastSuccess(__('Subcontratación eliminada.'));
    }

    public function toggleInvoiced(int $id): void
    {
        $record = $this->findOwned(Subcontracting::class, $id);
        $record->update(['invoiced' => ! $record->invoiced]);
        $this->toastSuccess($record->invoiced
            ? __('Marcado como facturado.')
            : __('Marcado como pendiente de factura.'));
    }

    protected function baseQuery(): Builder
    {
        return Subcontracting::where('viticulturist_id', $this->viticulturistId())
            ->with(['plot', 'campaign']);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->filter_campaign_id) {
            $query->where('campaign_id', $this->filter_campaign_id);
        }
        if ($this->filter_plot_id) {
            $query->where('plot_id', $this->filter_plot_id);
        }
        if ($this->filter_service_type) {
            $query->where('service_type', $this->filter_service_type);
        }
        if ($this->filter_invoiced !== '') {
            $query->where('invoiced', (bool) $this->filter_invoiced);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['service_date', 'desc'];
    }

    protected function filterDefaults(): array
    {
        return [
            'filter_campaign_id' => '',
            'filter_plot_id' => '',
            'filter_service_type' => '',
            'filter_invoiced' => '',
        ];
    }

    protected function viewData(mixed $entries): array
    {
        $viticulturistId = $this->viticulturistId();
        $base = Subcontracting::where('viticulturist_id', $viticulturistId);
        $stats = [
            'total' => (clone $base)->count(),
            'total_amount' => (clone $base)->whereNotNull('amount')->sum('amount'),
            'invoiced' => (clone $base)->where('invoiced', true)->count(),
            'pending' => (clone $base)->where('invoiced', false)->count(),
        ];

        return [
            'records' => $entries,
            'stats' => $stats,
            'campaigns' => Campaign::where('viticulturist_id', $viticulturistId)->orderByDesc('year')->get(),
            'plots' => Plot::where('viticulturist_id', $viticulturistId)->orderBy('name')->get(),
            'serviceTypes' => Subcontracting::serviceTypeOptions(),
        ];
    }
}
