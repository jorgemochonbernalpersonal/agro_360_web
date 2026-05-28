<?php

namespace App\Livewire\Viticulturist\Certifications;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Certification;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $currentTab             = 'active';
    public string $search                 = '';
    public string $filterCertificationType = '';

    protected $queryString = [
        'currentTab'              => ['as' => 'tab',  'except' => 'active'],
        'search'                  => ['as' => 'q',    'except' => ''],
        'filterCertificationType' => ['as' => 'type', 'except' => ''],
    ];

    public function updatingSearch(): void                  { $this->resetPage(); }
    public function updatingFilterCertificationType(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'filterCertificationType' => ''];
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function archive(int $id): void
    {
        $this->findOwned(Certification::class, $id)->update(['active' => false]);
        $this->toastSuccess(__('Certificación archivada.'));
    }

    public function unarchive(int $id): void
    {
        $this->findOwned(Certification::class, $id)->update(['active' => true]);
        $this->toastSuccess(__('Certificación restaurada.'));
    }

    public function delete(int $id): void
    {
        $this->findOwned(Certification::class, $id)->delete();
        $this->toastSuccess(__('Certificación eliminada.'));
    }

    protected function baseQuery(): Builder
    {
        return Certification::where('viticulturist_id', $this->viticulturistId())
            ->where('active', $this->currentTab === 'active');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('certifying_body', 'like', '%' . $this->search . '%')
                  ->orWhere('certificate_number', 'like', '%' . $this->search . '%')
                  ->orWhere('scope', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->filterCertificationType) {
            $query->where('certification_type', $this->filterCertificationType);
        }
    }

    protected function defaultOrderBy(): array { return ['issue_date', 'desc']; }
    protected function perPage(): int          { return 15; }

    protected function viewData(mixed $entries): array
    {
        $userId    = $this->viticulturistId();
        $baseQuery = Certification::where('viticulturist_id', $userId);
        $activeQ   = (clone $baseQuery)->where('active', true);

        $now = now();
        $stats = [
            'active'         => $activeQ->count(),
            'archived'       => (clone $baseQuery)->where('active', false)->count(),
            'expiring_soon'  => (clone $activeQ)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>', $now)
                ->whereDate('expiry_date', '<=', $now->copy()->addDays(60))
                ->count(),
            'expired'        => (clone $activeQ)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', $now)
                ->count(),
        ];

        return [
            'entries'              => $entries,
            'certificationTypes'   => Certification::certificationTypeOptions(),
            'stats'                => $stats,
        ];
    }
}
