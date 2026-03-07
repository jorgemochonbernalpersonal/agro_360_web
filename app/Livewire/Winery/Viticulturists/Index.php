<?php

namespace App\Livewire\Winery\Viticulturists;

use App\Livewire\Winery\AbstractIndex;
use App\Models\WineryViticulturist;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search = '';

    protected function filterDefaults(): array
    {
        return ['search' => ''];
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
    }

    protected function defaultOrderBy(): array
    {
        return ['created_at', 'desc'];
    }

    protected function viewData(mixed $entries): array
    {
        return ['relations' => $entries];
    }
}
