<?php

namespace App\Livewire\Supervisor;

use App\Models\Certification;
use App\Models\DoInspection;
use App\Models\DoLabel;
use App\Models\DoQualification;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorRequest;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $doId = Auth::id();

        // ── Top stats ─────────────────────────────────────────────────────────

        $wineryCount = SupervisorWinery::where('supervisor_id', $doId)->count();

        $viticulturistCount = SupervisorViticulturist::where('supervisor_id', $doId)->count();

        $pendingQualifications = DoQualification::forSupervisor($doId)
            ->where('result', DoQualification::RESULT_PENDING)
            ->count();

        $issuedLabelsThisYear = (int) DoLabel::forSupervisor($doId)
            ->where('status', DoLabel::STATUS_ISSUED)
            ->whereYear('issued_at', now()->year)
            ->sum('quantity_issued');

        $pendingLabels = DoLabel::forSupervisor($doId)
            ->whereIn('status', [DoLabel::STATUS_PENDING, DoLabel::STATUS_APPROVED])
            ->count();

        $nonCompliantInspections = DoInspection::forSupervisor($doId)
            ->where('result', DoInspection::RESULT_NON_COMPLIANT)
            ->whereNotIn('status', [DoInspection::STATUS_CANCELLED])
            ->count();

        $pendingRequests = SupervisorRequest::forSupervisor($doId)
            ->whereIn('status', [SupervisorRequest::STATUS_PENDING, SupervisorRequest::STATUS_IN_REVIEW])
            ->count();

        $overdueRequests = SupervisorRequest::forSupervisor($doId)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->whereNotIn('status', [SupervisorRequest::STATUS_APPROVED, SupervisorRequest::STATUS_REJECTED, SupervisorRequest::STATUS_ARCHIVED])
            ->count();

        // ── Upcoming inspections (next 21 days) ───────────────────────────────

        $upcomingInspections = DoInspection::where('supervisor_id', $doId)
            ->where('status', DoInspection::STATUS_SCHEDULED)
            ->whereBetween('inspection_date', [today(), today()->addDays(21)])
            ->orderBy('inspection_date')
            ->limit(5)
            ->get();

        // ── Pending notebook access requests (with viticulturist name) ────────

        $pendingNotebookRequests = NotebookAccessRequest::where('supervisor_id', $doId)
            ->where('status', NotebookAccessRequest::STATUS_PENDING)
            ->with('viticulturist:id,name,email')
            ->latest('requested_at')
            ->limit(5)
            ->get();

        $pendingNotebookCount = $pendingNotebookRequests->count();

        // ── Expiring certifications in the pool (next 60 days) ───────────────

        $poolIds = SupervisorViticulturist::where('supervisor_id', $doId)
            ->pluck('viticulturist_id');

        $expiringCertifications = Certification::whereIn('viticulturist_id', $poolIds)
            ->where('active', true)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [today(), today()->addDays(60)])
            ->with('viticulturist:id,name')
            ->orderBy('expiry_date')
            ->limit(5)
            ->get();

        // ── Recent supervisor requests ─────────────────────────────────────────

        $recentRequests = SupervisorRequest::forSupervisor($doId)
            ->whereIn('status', [SupervisorRequest::STATUS_PENDING, SupervisorRequest::STATUS_IN_REVIEW])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return view('livewire.supervisor.dashboard', [
            'wineryCount' => $wineryCount,
            'viticulturistCount' => $viticulturistCount,
            'pendingQualifications' => $pendingQualifications,
            'issuedLabelsThisYear' => $issuedLabelsThisYear,
            'pendingLabels' => $pendingLabels,
            'nonCompliantInspections' => $nonCompliantInspections,
            'pendingRequests' => $pendingRequests,
            'overdueRequests' => $overdueRequests,
            'upcomingInspections' => $upcomingInspections,
            'pendingNotebookRequests' => $pendingNotebookRequests,
            'pendingNotebookCount' => $pendingNotebookCount,
            'expiringCertifications' => $expiringCertifications,
            'recentRequests' => $recentRequests,
        ])->layout('layouts.app');
    }
}
