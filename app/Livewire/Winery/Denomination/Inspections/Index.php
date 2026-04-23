<?php

namespace App\Livewire\Winery\Denomination\Inspections;

use App\Models\DoInspection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool   $embedded     = false;
    public string $statusFilter = '';

    protected function queryString(): array
    {
        if ($this->embedded) return [];
        return ['statusFilter' => ['except' => '']];
    }

    public function updatingStatusFilter(): void { $this->resetPage(); }

    #[Layout('layouts.app')]
    public function render()
    {
        $wineryId = Auth::id();

        $query = DoInspection::forWinery($wineryId)->with('supervisor:id,name');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $inspections = $query->orderByDesc('inspection_date')->paginate(15);

        $statusCounts = DoInspection::forWinery($wineryId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'all'         => $statusCounts->sum(),
            'scheduled'   => $statusCounts->get('scheduled', 0),
            'in_progress' => $statusCounts->get('in_progress', 0),
            'completed'   => $statusCounts->get('completed', 0),
        ];

        return view('livewire.winery.denomination.inspections.index', [
            'inspections'  => $inspections,
            'counts'       => $counts,
            'statusLabels' => DoInspection::STATUS_LABELS,
            'resultLabels' => DoInspection::RESULT_LABELS,
            'resultColors' => DoInspection::RESULT_COLORS,
        ]);
    }
}
