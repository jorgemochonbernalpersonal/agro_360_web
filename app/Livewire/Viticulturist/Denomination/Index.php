<?php

namespace App\Livewire\Viticulturist\Denomination;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\DoDocument;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorViticulturist;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications;

    public function revokeNotebookAccess(): void
    {
        $viticulturistId = Auth::id();

        $relation = SupervisorViticulturist::where('viticulturist_id', $viticulturistId)
            ->where('notebook_access', true)
            ->first();

        if (!$relation) {
            $this->toastError(__('No tienes acceso al cuaderno concedido a tu DO.'));
            return;
        }

        $relation->update([
            'notebook_access'     => false,
            'notebook_revoked_at' => now(),
        ]);

        // Update pending/approved access request to reflect revocation
        NotebookAccessRequest::where('supervisor_id', $relation->supervisor_id)
            ->where('viticulturist_id', $viticulturistId)
            ->whereIn('status', [NotebookAccessRequest::STATUS_PENDING, NotebookAccessRequest::STATUS_APPROVED])
            ->update([
                'status'       => NotebookAccessRequest::STATUS_REJECTED,
                'responded_at' => now(),
            ]);

        Cache::forget("nav_badge_notebook_access_{$viticulturistId}");

        $this->toastSuccess(__('Acceso al cuaderno revocado. Tu denominación ya no puede ver tus anotaciones.'));
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $viticulturistId = Auth::id();

        $supervisorRelation = SupervisorViticulturist::where('viticulturist_id', $viticulturistId)
            ->with('supervisor')
            ->first();

        $supervisor = $supervisorRelation?->supervisor;

        $supervisorWineries = collect();
        $doDocuments        = collect();
        $pendingRequest     = null;

        if ($supervisor) {
            $supervisorWineries = WineryViticulturist::where('viticulturist_id', $viticulturistId)
                ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
                ->where('supervisor_id', $supervisor->id)
                ->with('winery')
                ->get()
                ->map(fn ($rel) => $rel->winery)
                ->filter();

            // Active DO documents (pliego, reglamento) published by this DO
            $doDocuments = DoDocument::where('supervisor_id', $supervisor->id)
                ->where('status', DoDocument::STATUS_ACTIVE)
                ->orderByDesc('effective_date')
                ->get();

            // Pending notebook access request from this DO (if not already granted)
            if (!$supervisorRelation->hasNotebookAccess()) {
                $pendingRequest = NotebookAccessRequest::where('supervisor_id', $supervisor->id)
                    ->where('viticulturist_id', $viticulturistId)
                    ->where('status', NotebookAccessRequest::STATUS_PENDING)
                    ->first();
            }
        }

        $notebookGranted = $supervisorRelation?->hasNotebookAccess() ?? false;

        return view('livewire.viticulturist.denomination.index', [
            'supervisor'         => $supervisor,
            'supervisorJoined'   => $supervisorRelation?->created_at,
            'supervisorWineries' => $supervisorWineries,
            'notebookGranted'    => $notebookGranted,
            'doDocuments'        => $doDocuments,
            'pendingRequest'     => $pendingRequest,
        ]);
    }
}
