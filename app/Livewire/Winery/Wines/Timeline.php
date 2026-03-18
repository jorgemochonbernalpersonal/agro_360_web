<?php

namespace App\Livewire\Winery\Wines;

use App\Models\Wine;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Timeline extends Component
{
    #[Url]
    public string $vintageFilter = '';

    #[Url]
    public string $typeFilter = '';

    public function render()
    {
        $wines = Wine::forUser(Auth::id())
            ->with([
                'processDetails' => fn($q) => $q->orderBy('start_date'),
                'oenologist',
                'bottlings',
            ])
            ->whereIn('status', ['in_progress', 'aged', 'bottled'])
            ->when($this->vintageFilter, fn($q) => $q->where('vintage', $this->vintageFilter))
            ->when($this->typeFilter, fn($q) => $q->where('wine_type', $this->typeFilter))
            ->orderBy('vintage', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($wine) {
                $latestProcess = $wine->processDetails->last();

                $wine->current_phase_label = $latestProcess?->process_type_label ?? 'Sin proceso registrado';
                $wine->current_phase_type  = $latestProcess?->process_type ?? 'pending';
                $wine->days_total          = (int) now()->diffInDays($wine->created_at);
                $wine->days_in_phase       = $latestProcess?->start_date
                    ? (int) now()->diffInDays($latestProcess->start_date)
                    : $wine->days_total;

                return $wine;
            });

        $grouped = $wines->groupBy('status');

        $vintages = Wine::forUser(Auth::id())
            ->whereNotNull('vintage')
            ->distinct()
            ->orderBy('vintage', 'desc')
            ->pluck('vintage');

        $statusLabels = [
            'in_progress' => 'En Proceso',
            'aged'        => 'En Crianza',
            'bottled'     => 'Embotellado',
        ];

        $statusColors = [
            'in_progress' => 'blue',
            'aged'        => 'amber',
            'bottled'     => 'agro',
        ];

        return view('livewire.winery.wines.timeline', [
            'grouped'      => $grouped,
            'vintages'     => $vintages,
            'types'        => Wine::WINE_TYPES,
            'statusLabels' => $statusLabels,
            'statusColors' => $statusColors,
        ]);
    }
}
