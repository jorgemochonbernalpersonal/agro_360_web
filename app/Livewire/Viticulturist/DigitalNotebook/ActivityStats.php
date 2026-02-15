<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Repositories\AgriculturalActivityRepository;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ActivityStats extends Component
{
    public $selectedCampaign;
    public $filters = [];

    protected $listeners = [
        'filters-updated' => 'updateStats',
        'activity-deleted' => 'updateStats',
    ];

    public function mount($selectedCampaign, $filters = [])
    {
        $this->selectedCampaign = $selectedCampaign;
        $this->filters = $filters;
    }

    public function render()
    {
        $repository = app(AgriculturalActivityRepository::class);
        $user = Auth::user();

        $stats = $repository->getStatsForCampaign(
            $user->id,
            $this->selectedCampaign,
            $this->filters
        );

        return view('livewire.viticulturist.digital-notebook.activity-stats', [
            'stats' => $stats,
        ]);
    }

    public function updateStats($filters = [])
    {
        $this->filters = $filters;
    }
}
