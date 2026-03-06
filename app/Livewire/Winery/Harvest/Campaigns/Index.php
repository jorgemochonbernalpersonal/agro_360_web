<?php

namespace App\Livewire\Winery\Harvest\Campaigns;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $search = '';
    public string $yearFilter = '';

    protected $queryString = [
        'search'     => ['except' => ''],
        'yearFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingYearFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search     = '';
        $this->yearFilter = '';
        $this->resetPage();
    }

    public function toggleActive(int $campaignId): void
    {
        $campaign = Campaign::forViticulturist(Auth::id())->findOrFail($campaignId);

        if ($campaign->active) {
            $campaign->update(['active' => false]);
            $this->toastSuccess("Campaña {$campaign->year} cerrada.");
        } else {
            $campaign->activate();
            $this->toastSuccess("Campaña {$campaign->year} activada como campaña actual.");
        }
    }

    public function delete(int $campaignId): void
    {
        $campaign = Campaign::withCount('activities')
            ->forViticulturist(Auth::id())
            ->findOrFail($campaignId);

        if ($campaign->activities_count > 0) {
            $this->toastError('No se puede eliminar una campaña con recepciones registradas.');
            return;
        }

        $campaign->delete();
        $this->toastSuccess('Campaña eliminada correctamente.');
    }

    public function render()
    {
        $query = Campaign::forViticulturist(Auth::id())
            ->withCount('activities')
            ->orderBy('year', 'desc');

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
            });
        }

        if ($this->yearFilter) {
            $query->forYear((int) $this->yearFilter);
        }

        $campaigns = $query->paginate(15);

        $years = Campaign::forViticulturist(Auth::id())
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $stats = [
            'active' => Campaign::forViticulturist(Auth::id())->active()->count(),
            'total'  => Campaign::forViticulturist(Auth::id())->count(),
        ];

        return view('livewire.winery.harvest.campaigns.index', [
            'campaigns' => $campaigns,
            'years'     => $years,
            'stats'     => $stats,
        ])->layout('layouts.app');
    }
}
