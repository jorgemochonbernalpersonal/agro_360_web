<?php

namespace App\Livewire\Winery\Denomination\Qualifications;

use App\Models\DoQualification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $resultFilter  = '';
    public string $vintageFilter = '';

    protected $queryString = [
        'resultFilter'  => ['except' => ''],
        'vintageFilter' => ['except' => ''],
    ];

    public function updatingResultFilter(): void { $this->resetPage(); }
    public function updatingVintageFilter(): void { $this->resetPage(); }

    #[Layout('layouts.app')]
    public function render()
    {
        $wineryId = Auth::id();

        $query = DoQualification::forWinery($wineryId)->with('supervisor:id,name');

        if ($this->resultFilter) {
            $query->where('result', $this->resultFilter);
        }
        if ($this->vintageFilter) {
            $query->where('vintage', $this->vintageFilter);
        }

        $qualifications = $query->orderByDesc('qualification_date')->paginate(15);

        $resultCounts = DoQualification::forWinery($wineryId)
            ->selectRaw('result, count(*) as total')
            ->groupBy('result')
            ->pluck('total', 'result');

        $counts = [
            'all'          => $resultCounts->sum(),
            'pending'      => $resultCounts->get('pending', 0),
            'qualified'    => $resultCounts->get('qualified', 0),
            'disqualified' => $resultCounts->get('disqualified', 0),
        ];

        $availableVintages = DoQualification::forWinery($wineryId)
            ->select('vintage')->distinct()->orderByDesc('vintage')->pluck('vintage');

        return view('livewire.winery.denomination.qualifications.index', [
            'qualifications'    => $qualifications,
            'counts'            => $counts,
            'availableVintages' => $availableVintages,
            'resultLabels'      => DoQualification::RESULT_LABELS,
            'resultColors'      => DoQualification::RESULT_COLORS,
            'colorLabels'       => DoQualification::COLOR_LABELS,
        ]);
    }
}
