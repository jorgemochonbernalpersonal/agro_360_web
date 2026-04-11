<?php

namespace App\Livewire\Supervisor;

use App\Models\DoLabel;
use App\Models\DoQualification;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorRequest;
use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $doId = Auth::id();

        $wineryCount = SupervisorWinery::where('supervisor_id', $doId)->count();

        $viticulturistCount = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->distinct('viticulturist_id')
            ->count('viticulturist_id');

        $pendingQualifications = DoQualification::forSupervisor($doId)
            ->where('result', DoQualification::RESULT_PENDING)
            ->count();

        $issuedLabelsThisYear = (int) DoLabel::forSupervisor($doId)
            ->where('status', DoLabel::STATUS_ISSUED)
            ->whereYear('issued_at', now()->year)
            ->sum('quantity_issued');

        $pendingNotebookRequests = NotebookAccessRequest::where('supervisor_id', $doId)
            ->where('status', NotebookAccessRequest::STATUS_PENDING)
            ->count();

        $pendingRequests = SupervisorRequest::forSupervisor($doId)
            ->whereIn('status', [SupervisorRequest::STATUS_PENDING, SupervisorRequest::STATUS_IN_REVIEW])
            ->count();

        $overdueRequests = SupervisorRequest::forSupervisor($doId)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->whereNotIn('status', [SupervisorRequest::STATUS_APPROVED, SupervisorRequest::STATUS_REJECTED, SupervisorRequest::STATUS_ARCHIVED])
            ->count();

        return view('livewire.supervisor.dashboard', [
            'wineryCount'              => $wineryCount,
            'viticulturistCount'       => $viticulturistCount,
            'pendingQualifications'    => $pendingQualifications,
            'issuedLabelsThisYear'     => $issuedLabelsThisYear,
            'pendingNotebookRequests'  => $pendingNotebookRequests,
            'pendingRequests'          => $pendingRequests,
            'overdueRequests'          => $overdueRequests,
        ])->layout('layouts.app');
    }
}
