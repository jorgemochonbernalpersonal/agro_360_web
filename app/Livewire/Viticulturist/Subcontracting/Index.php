<?php

namespace App\Livewire\Viticulturist\Subcontracting;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\Subcontracting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $filter_campaign_id = '';
    public string $filter_plot_id = '';
    public string $filter_service_type = '';
    public string $filter_invoiced = '';

    protected $queryString = [
        'filter_campaign_id'   => ['except' => '', 'as' => 'campaign'],
        'filter_plot_id'       => ['except' => '', 'as' => 'plot'],
        'filter_service_type'  => ['except' => '', 'as' => 'type'],
        'filter_invoiced'      => ['except' => '', 'as' => 'invoiced'],
    ];

    public function updatingFilterCampaignId(): void { $this->resetPage(); }
    public function updatingFilterPlotId(): void { $this->resetPage(); }
    public function updatingFilterServiceType(): void { $this->resetPage(); }
    public function updatingFilterInvoiced(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filter_campaign_id  = '';
        $this->filter_plot_id      = '';
        $this->filter_service_type = '';
        $this->filter_invoiced     = '';
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Subcontracting::where('viticulturist_id', Auth::id())->findOrFail($id)->delete();
        $this->toastSuccess(__('Subcontratación eliminada.'));
    }

    public function toggleInvoiced(int $id): void
    {
        $record = Subcontracting::where('viticulturist_id', Auth::id())->findOrFail($id);
        $record->update(['invoiced' => !$record->invoiced]);
        $this->toastSuccess($record->invoiced ? __('Marcado como facturado.') : __('Marcado como pendiente de factura.'));
    }

    public function render()
    {
        $user = Auth::user();

        $query = Subcontracting::where('viticulturist_id', $user->id)
            ->with(['plot', 'campaign']);

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

        $records = $query->orderByDesc('service_date')->paginate(20);

        $base = Subcontracting::where('viticulturist_id', $user->id);
        $stats = [
            'total'        => (clone $base)->count(),
            'total_amount' => (clone $base)->whereNotNull('amount')->sum('amount'),
            'invoiced'     => (clone $base)->where('invoiced', true)->count(),
            'pending'      => (clone $base)->where('invoiced', false)->count(),
        ];

        $campaigns    = Campaign::where('viticulturist_id', $user->id)->orderByDesc('year')->get();
        $plots        = Plot::where('viticulturist_id', $user->id)->orderBy('name')->get();

        return view('livewire.viticulturist.subcontracting.index', [
            'records'      => $records,
            'stats'        => $stats,
            'campaigns'    => $campaigns,
            'plots'        => $plots,
            'serviceTypes' => Subcontracting::serviceTypeOptions(),
        ])->layout('layouts.app');
    }
}
