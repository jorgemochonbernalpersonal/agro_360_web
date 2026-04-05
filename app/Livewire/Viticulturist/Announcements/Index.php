<?php

namespace App\Livewire\Viticulturist\Announcements;

use App\Models\WineryAnnouncement;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render()
    {
        $user = Auth::user();

        $announcements = WineryAnnouncement::active()
            ->visibleTo($user)
            ->with('winery:id,name')
            ->orderByDesc('published_at')
            ->paginate(20);

        return view('livewire.viticulturist.announcements.index', [
            'announcements' => $announcements,
        ])->layout('layouts.app');
    }
}
