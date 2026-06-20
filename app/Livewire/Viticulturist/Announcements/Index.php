<?php

namespace App\Livewire\Viticulturist\Announcements;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\WineryAnnouncement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Index extends AbstractIndex
{
    protected function baseQuery(): Builder
    {
        return WineryAnnouncement::active()
            ->visibleTo(Auth::user())
            ->with('winery:id,name');
    }

    protected function defaultOrderBy(): array
    {
        return ['published_at', 'desc'];
    }

    protected function viewData(mixed $entries): array
    {
        return ['announcements' => $entries];
    }
}
