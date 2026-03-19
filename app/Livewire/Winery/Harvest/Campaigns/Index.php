<?php

namespace App\Livewire\Winery\Harvest\Campaigns;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search     = '';
    public string $yearFilter = '';

    protected $queryString = [
        'search'     => ['except' => ''],
        'yearFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingYearFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'yearFilter' => ''];
    }

    public function toggleActive(int $campaignId): void
    {
        $campaign = Campaign::forViticulturist($this->wineryId())->findOrFail($campaignId);

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
            ->forViticulturist($this->wineryId())
            ->findOrFail($campaignId);

        if ($campaign->activities_count > 0) {
            $this->toastError('No se puede eliminar una campaña con recepciones registradas.');
            return;
        }

        $campaign->delete();
        $this->toastSuccess('Campaña eliminada correctamente.');
    }

    protected function baseQuery(): Builder
    {
        return Campaign::forViticulturist($this->wineryId())->withCount('activities');
    }

    protected function applyFilters(Builder $query): void
    {
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
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderBy('year', 'desc');
    }

    protected function defaultOrderBy(): array { return ['year', 'desc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        $years = Campaign::forViticulturist($this->wineryId())
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $base = Campaign::forViticulturist($this->wineryId());
        $stats = [
            'total'    => (clone $base)->count(),
            'active'   => (clone $base)->where('active', true)->count(),
            'cerradas' => (clone $base)->where('active', false)->count(),
            'locked'   => (clone $base)->whereNotNull('locked_at')->count(),
        ];

        return [
            'campaigns' => $entries,
            'years'     => $years,
            'stats'     => $stats,
        ];
    }
}
