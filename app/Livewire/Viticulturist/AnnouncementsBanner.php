<?php

namespace App\Livewire\Viticulturist;

use App\Models\WineryAnnouncement;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AnnouncementsBanner extends Component
{
    public function render()
    {
        $user = Auth::user();

        $announcements = WineryAnnouncement::active()
            ->visibleTo($user)
            ->with('winery:id,name')
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('livewire.viticulturist.announcements-banner', [
            'announcements' => $announcements,
        ]);
    }
}
