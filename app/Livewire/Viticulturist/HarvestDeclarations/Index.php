<?php

namespace App\Livewire\Viticulturist\HarvestDeclarations;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\HarvestDeclaration;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $filterCampaign = '';

    public string $filterStatus = '';

    protected $queryString = [
        'search' => ['as' => 'q',        'except' => ''],
        'filterCampaign' => ['as' => 'campaign',  'except' => ''],
        'filterStatus' => ['as' => 'status',    'except' => ''],
    ];

    public function mount(): void
    {
        if ($this->filterCampaign === '') {
            $campaign = Campaign::getOrCreateActiveForYear($this->viticulturistId());
            $this->filterCampaign = (string) ($campaign?->id ?? '');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCampaign(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $record = $this->findOwned(HarvestDeclaration::class, $id);
        if (! $record->isDraft()) {
            $this->toastError(__('Solo se pueden eliminar declaraciones en borrador.'));

            return;
        }
        $record->delete();
        $this->toastSuccess(__('Declaración eliminada.'));
    }

    public function markSubmitted(int $id): void
    {
        $record = $this->findOwned(HarvestDeclaration::class, $id);
        $record->update([
            'status' => 'submitted',
            'submission_date' => now()->toDateString(),
        ]);
        $this->toastSuccess(__('Declaración marcada como presentada.'));
    }

    public function markAccepted(int $id): void
    {
        $this->findOwned(HarvestDeclaration::class, $id)->update(['status' => 'accepted']);
        $this->toastSuccess(__('Declaración marcada como aceptada.'));
    }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'filterCampaign' => '', 'filterStatus' => ''];
    }

    protected function baseQuery(): Builder
    {
        return HarvestDeclaration::where('viticulturist_id', $this->viticulturistId())
            ->where('active', true);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('authority', 'like', '%'.$this->search.'%')
                    ->orWhere('reference_number', 'like', '%'.$this->search.'%')
                    ->orWhere('declaration_year', 'like', '%'.$this->search.'%');
            });
        }
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['declaration_year', 'desc'];
    }

    protected function perPage(): int
    {
        return 15;
    }

    protected function viewData(mixed $entries): array
    {
        $userId = $this->viticulturistId();
        $baseQuery = HarvestDeclaration::where('viticulturist_id', $userId)->where('active', true);

        $stats = [
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'submitted' => (clone $baseQuery)->where('status', 'submitted')->count(),
            'accepted' => (clone $baseQuery)->where('status', 'accepted')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        return [
            'entries' => $entries,
            'campaigns' => Campaign::forViticulturist($userId)->orderByDesc('year')->get(),
            'statuses' => HarvestDeclaration::statusOptions(),
            'stats' => $stats,
        ];
    }
}
