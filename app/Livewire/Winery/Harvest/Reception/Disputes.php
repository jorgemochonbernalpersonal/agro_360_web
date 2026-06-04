<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Models\HarvestDelivery;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Disputes extends Component
{
    public string $vintageFilter = '';

    public string $noteFilter = ''; // '' | 'with_note' | 'without_note'

    protected $queryString = [
        'vintageFilter' => ['except' => ''],
        'noteFilter' => ['except' => ''],
    ];

    public function render()
    {
        $wineryId = Auth::id();

        $disputes = HarvestDelivery::with([
            'viticulturist',
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'harvest',
        ])
            ->whereHas('harvest', fn ($q) => $q->where('winery_id', $wineryId))
            ->where('status', 'disputed')
            ->when($this->vintageFilter, fn ($q) => $q->where('vintage_year', (int) $this->vintageFilter))
            ->when($this->noteFilter === 'with_note', fn ($q) => $q->whereNotNull('dispute_submitted_at'))
            ->when($this->noteFilter === 'without_note', fn ($q) => $q->whereNull('dispute_submitted_at'))
            ->orderByDesc('dispute_submitted_at')
            ->orderByDesc('created_at')
            ->get();

        $vintageYears = HarvestDelivery::whereHas('harvest', fn ($q) => $q->where('winery_id', $wineryId))
            ->where('status', 'disputed')
            ->distinct()
            ->pluck('vintage_year')
            ->sortDesc()
            ->values();

        return view('livewire.winery.harvest.reception.disputes', [
            'disputes' => $disputes,
            'vintageYears' => $vintageYears,
        ])->layout('layouts.app', [
            'title' => __('Disputas abiertas - Agro365'),
            'description' => __('Entregas con diferencias pendientes de resolver.'),
        ]);
    }
}
