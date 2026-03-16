<?php

namespace App\Livewire\Viticulturist\WineryAccess;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\NotebookAccessRequest;
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

        $relation = WineryViticulturist::where('winery_id', $request->winery_id)
            ->where('viticulturist_id', Auth::id())
            ->first();

        if (!$relation) {
            $this->toastError('No se encontró la relación con esta bodega.');
            return;
        }

        $request->update([
            'status'       => NotebookAccessRequest::STATUS_APPROVED,
            'responded_at' => now(),
        ]);

        $relation->grantNotebookAccess();

        Cache::forget('nav_badge_notebook_access_' . Auth::id());

        $request->winery->notify(new NotebookAccessRespondedNotification(Auth::user(), NotebookAccessRequest::STATUS_APPROVED));

        $this->toastSuccess("Acceso al cuaderno concedido a {$request->winery->name}.");
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

        $request->winery->notify(new NotebookAccessRespondedNotification(Auth::user(), NotebookAccessRequest::STATUS_REJECTED));

        $this->toastSuccess("Solicitud de {$request->winery->name} rechazada.");
    }

    public function revoke(int $wineryId): void
    {
        $relation = WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', Auth::id())
            ->where('cuaderno_access', true)
            ->firstOrFail();

        $relation->revokeNotebookAccess();

        NotebookAccessRequest::where('winery_id', $wineryId)
            ->where('viticulturist_id', Auth::id())
            ->update(['status' => NotebookAccessRequest::STATUS_REJECTED, 'responded_at' => now()]);

        $relation->winery->notify(new NotebookAccessRespondedNotification(Auth::user(), NotebookAccessRequest::STATUS_REJECTED));

        $this->toastSuccess('Acceso al cuaderno revocado.');
    }

    #[Layout('layouts.app', [
        'title'       => 'Notebook Access - Agro365',
        'description' => 'Gestiona qué bodegas pueden ver tu cuaderno de campo digital.',
    ])]
    public function render()
    {
        $viticulturistId = Auth::id();

        $pending = NotebookAccessRequest::with('winery')
            ->where('viticulturist_id', $viticulturistId)
            ->where('status', NotebookAccessRequest::STATUS_PENDING)
            ->orderBy('requested_at', 'desc')
            ->get();

        $granted = WineryViticulturist::with('winery')
            ->where('viticulturist_id', $viticulturistId)
            ->where('cuaderno_access', true)
            ->orderBy('cuaderno_granted_at', 'desc')
            ->get();

        $rejected = NotebookAccessRequest::with('winery')
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
