<?php

namespace App\Livewire\Viticulturist\OfficialReports;

use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Viticulturist\OfficialReports\Traits\WithInvalidateReportModal;
use App\Livewire\Viticulturist\OfficialReports\Traits\WithPreviewReportModal;
use App\Livewire\Viticulturist\OfficialReports\Traits\WithShareReportModal;
use App\Models\OfficialReport;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithInvalidateReportModal, WithPagination, WithPreviewReportModal, WithShareReportModal, WithToastNotifications;

    public $search = '';

    public $statusFilter = 'all';

    public $hasPendingReports = false;

    public function mount(): void
    {
        $this->checkPendingReports();
    }

    public function checkPendingReports(): void
    {
        $this->hasPendingReports = OfficialReport::forUser(auth()->id())
            ->whereIn('processing_status', ['pending', 'processing'])
            ->exists();
    }

    public function switchTab(string $filter): void
    {
        $this->statusFilter = $filter;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->resetPage();
    }

    public function render()
    {
        $query = OfficialReport::forUser(auth()->id())->with('user');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('verification_code', 'like', '%'.$this->search.'%')
                    ->orWhere('report_type', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter === 'valid') {
            $query->where('is_valid', true);
        } elseif ($this->statusFilter === 'invalid') {
            $query->where('is_valid', false);
        }

        $reports = $query->recent()->paginate(15);

        $baseQuery = OfficialReport::forUser(auth()->id());
        $totalCount = $baseQuery->count();
        $validCount = (clone $baseQuery)->where('is_valid', true)->count();
        $invalidCount = (clone $baseQuery)->where('is_valid', false)->count();
        $pendingCount = (clone $baseQuery)->where('processing_status', 'pending')->count();
        $processingCount = (clone $baseQuery)->where('processing_status', 'processing')->count();
        $lastReport = $baseQuery->recent()->first();

        $this->hasPendingReports = ($pendingCount + $processingCount) > 0;

        return view('livewire.viticulturist.official-reports.index', [
            'reports' => $reports,
            'totalCount' => $totalCount,
            'validCount' => $validCount,
            'invalidCount' => $invalidCount,
            'pendingCount' => $pendingCount,
            'processingCount' => $processingCount,
            'lastReportDate' => $lastReport ? $lastReport->created_at->format('d/m/Y') : null,
        ]);
    }
}
