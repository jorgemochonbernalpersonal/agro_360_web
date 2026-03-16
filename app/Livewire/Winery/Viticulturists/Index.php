<?php

namespace App\Livewire\Winery\Viticulturists;

use App\Exports\ViticulturistExport;
use App\Livewire\Winery\AbstractIndex;
use App\Models\WineryViticulturist;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class Index extends AbstractIndex
{
    public string $search       = '';
    public string $sourceFilter = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'sourceFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingSourceFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'sourceFilter' => ''];
    }

    protected function baseQuery(): Builder
    {
        return WineryViticulturist::where('winery_id', $this->wineryId())
            ->with(['viticulturist' => fn($q) => $q->withCount('plots')->select([
                'id', 'name', 'email', 'can_login',
                'invitation_token', 'invitation_sent_at', 'invitation_expires_at',
            ])]);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $search = '%' . mb_strtolower($this->search) . '%';
            $query->whereHas('viticulturist', fn($q) =>
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$search])
            );
        }

        if ($this->sourceFilter) {
            $query->where('source', $this->sourceFilter);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['created_at', 'desc'];
    }

    public function export()
    {
        return Excel::download(
            new ViticulturistExport($this->wineryId()),
            'viticultores_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    protected function viewData(mixed $entries): array
    {
        return ['relations' => $entries];
    }
}
