<?php

namespace App\Livewire\Winery\Denomination\Labels;

use App\Models\DoLabel;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool   $embedded      = false;
    public string $statusFilter  = '';
    public string $vintageFilter = '';

    protected function queryString(): array
    {
        if ($this->embedded) return [];
        return [
            'statusFilter'  => ['except' => ''],
            'vintageFilter' => ['except' => ''],
        ];
    }

    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingVintageFilter(): void { $this->resetPage(); }

    #[Layout('layouts.app')]
    public function render()
    {
        $wineryId = Auth::id();

        $query = DoLabel::forWinery($wineryId)->with('supervisor:id,name');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->vintageFilter) {
            $query->where('vintage', $this->vintageFilter);
        }

        $labels = $query->orderByDesc('created_at')->paginate(15);

        $statusCounts = DoLabel::forWinery($wineryId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'all'      => $statusCounts->sum(),
            'pending'  => $statusCounts->get('pending', 0),
            'approved' => $statusCounts->get('approved', 0),
            'issued'   => $statusCounts->get('issued', 0),
        ];

        $availableVintages = DoLabel::forWinery($wineryId)
            ->select('vintage')->distinct()->orderByDesc('vintage')->pluck('vintage');

        return view('livewire.winery.denomination.labels.index', [
            'labels'            => $labels,
            'counts'            => $counts,
            'availableVintages' => $availableVintages,
            'statusLabels'      => DoLabel::statusLabelOptions(),
            'statusColors'      => DoLabel::STATUS_COLORS,
        ]);
    }
}
