<?php

namespace App\Livewire\Viticulturist\WineryAccess;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorViticulturist;
use App\Models\WineryViticulturist;
use App\Notifications\NotebookAccessRespondedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications;

    public function approve(int $requestId): void
    {
        $request = NotebookAccessRequest::where('id', $requestId)
            ->where('viticulturist_id', Auth::id())
            ->where('status', NotebookAccessRequest::STATUS_PENDING)
            ->firstOrFail();

        if ($request->isFromSupervisor()) {
            $relation = SupervisorViticulturist::where('supervisor_id', $request->supervisor_id)
                ->where('viticulturist_id', Auth::id())
                ->first();

            if (!$relation) {
                $this->toastError(__('No se encontró la relación con esta denominación de origen.'));
                return;
            }

            $relation->grantNotebookAccess();
        } else {
            $relation = WineryViticulturist::where('winery_id', $request->winery_id)
                ->where('viticulturist_id', Auth::id())
                ->first();

            if (!$relation) {
                $this->toastError(__('No se encontró la relación con esta bodega.'));
                return;
            }

            $relation->grantNotebookAccess();
        }

        $request->update([
            'status'       => NotebookAccessRequest::STATUS_APPROVED,
            'responded_at' => now(),
        ]);

        Cache::forget('nav_badge_notebook_access_' . Auth::id());

        $requester = $request->requester();
        $requester?->notify(new NotebookAccessRespondedNotification(Auth::user(), NotebookAccessRequest::STATUS_APPROVED));

        $this->toastSuccess("Acceso al cuaderno concedido a {$requester?->name}.");
    }

    public function reject(int $requestId): void
    {
        $request = NotebookAccessRequest::where('id', $requestId)
            ->where('viticulturist_id', Auth::id())
            ->where('status', NotebookAccessRequest::STATUS_PENDING)
            ->firstOrFail();

        $request->update([
            'status'       => NotebookAccessRequest::STATUS_REJECTED,
            'responded_at' => now(),
        ]);

        Cache::forget('nav_badge_notebook_access_' . Auth::id());

        $requester = $request->requester();
        $requester?->notify(new NotebookAccessRespondedNotification(Auth::user(), NotebookAccessRequest::STATUS_REJECTED));

        $this->toastSuccess("Solicitud de {$requester?->name} rechazada.");
    }

    public function revoke(int $id, string $type = 'winery'): void
    {
        if ($type === 'supervisor') {
            $relation = SupervisorViticulturist::where('supervisor_id', $id)
                ->where('viticulturist_id', Auth::id())
                ->where('notebook_access', true)
                ->firstOrFail();

            $relation->revokeNotebookAccess();

            NotebookAccessRequest::where('supervisor_id', $id)
                ->where('viticulturist_id', Auth::id())
                ->update(['status' => NotebookAccessRequest::STATUS_REJECTED, 'responded_at' => now()]);

            $relation->supervisor->notify(
                new NotebookAccessRespondedNotification(Auth::user(), NotebookAccessRequest::STATUS_REJECTED)
            );
        } else {
            $relation = WineryViticulturist::where('winery_id', $id)
                ->where('viticulturist_id', Auth::id())
                ->where('notebook_access', true)
                ->firstOrFail();

            $relation->revokeNotebookAccess();

            NotebookAccessRequest::where('winery_id', $id)
                ->where('viticulturist_id', Auth::id())
                ->update(['status' => NotebookAccessRequest::STATUS_REJECTED, 'responded_at' => now()]);

            $relation->winery->notify(
                new NotebookAccessRespondedNotification(Auth::user(), NotebookAccessRequest::STATUS_REJECTED)
            );
        }

        $this->toastSuccess(__('Acceso al cuaderno revocado.'));
    }

    #[Layout('layouts.app', [
        'title'       => 'Notebook Access - Agro365',
        'description' => 'Gestiona qué bodegas y denominaciones de origen pueden ver tu cuaderno de campo digital.',
    ])]
    public function render()
    {
        $viticulturistId = Auth::id();

        $pending = NotebookAccessRequest::with(['winery', 'supervisor'])
            ->where('viticulturist_id', $viticulturistId)
            ->where('status', NotebookAccessRequest::STATUS_PENDING)
            ->orderBy('requested_at', 'desc')
            ->get();

        // Granted: both winery and supervisor accesses normalized for the view
        $grantedWineries = WineryViticulturist::with('winery')
            ->where('viticulturist_id', $viticulturistId)
            ->where('notebook_access', true)
            ->orderBy('notebook_granted_at', 'desc')
            ->get()
            ->map(fn($r) => (object) [
                'id'         => $r->winery_id,
                'type'       => 'winery',
                'name'       => $r->winery?->name,
                'granted_at' => $r->notebook_granted_at,
            ])->toBase();

        $grantedSupervisors = SupervisorViticulturist::with('supervisor')
            ->where('viticulturist_id', $viticulturistId)
            ->where('notebook_access', true)
            ->orderBy('notebook_granted_at', 'desc')
            ->get()
            ->map(fn($r) => (object) [
                'id'         => $r->supervisor_id,
                'type'       => 'supervisor',
                'name'       => $r->supervisor?->name,
                'granted_at' => $r->notebook_granted_at,
            ])->toBase();

        $granted = $grantedWineries->merge($grantedSupervisors)
            ->sortByDesc('granted_at')
            ->values();

        $rejected = NotebookAccessRequest::with(['winery', 'supervisor'])
            ->where('viticulturist_id', $viticulturistId)
            ->where('status', NotebookAccessRequest::STATUS_REJECTED)
            ->orderBy('responded_at', 'desc')
            ->get();

        return view('livewire.viticulturist.winery-access.index', [
            'pending'  => $pending,
            'granted'  => $granted,
            'rejected' => $rejected,
        ]);
    }
}
