<?php

namespace App\Livewire\Viticulturist\WineryRequests;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\User;
use App\Models\WineryJoinRequest;
use App\Notifications\WineryJoinRequestedNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications;

    public string $searchQuery = '';

    public string $message = '';

    public bool $showSearch = false;

    public function openSearch(): void
    {
        $this->searchQuery = '';
        $this->message = '';
        $this->showSearch = true;
    }

    public function closeSearch(): void
    {
        $this->showSearch = false;
    }

    public function searchWineries(): array
    {
        $term = trim($this->searchQuery);
        if (strlen($term) < 2) {
            return [];
        }

        $viticulturistId = Auth::id();

        // Exclude wineries already linked or with a pending/approved request
        $alreadyLinked = \App\Models\WineryViticulturist::where('viticulturist_id', $viticulturistId)
            ->pluck('winery_id');

        $alreadyRequested = WineryJoinRequest::where('viticulturist_id', $viticulturistId)
            ->whereIn('status', [WineryJoinRequest::STATUS_PENDING, WineryJoinRequest::STATUS_APPROVED])
            ->pluck('winery_id');

        $exclude = $alreadyLinked->merge($alreadyRequested)->unique();

        $like = '%'.mb_strtolower($term).'%';

        return User::where('role', User::ROLE_WINERY)
            ->whereNotIn('id', $exclude)
            ->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$like]);
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    public function sendRequest(int $wineryId): void
    {
        $viticulturistId = Auth::id();

        $winery = User::where('id', $wineryId)->where('role', User::ROLE_WINERY)->firstOrFail();

        // Guard: already linked
        $alreadyLinked = \App\Models\WineryViticulturist::where('viticulturist_id', $viticulturistId)
            ->where('winery_id', $wineryId)
            ->exists();

        if ($alreadyLinked) {
            $this->toastError(__('Ya estás vinculado a esta bodega.'));

            return;
        }

        // Guard: already has pending or approved request
        $existing = WineryJoinRequest::where('viticulturist_id', $viticulturistId)
            ->where('winery_id', $wineryId)
            ->whereIn('status', [WineryJoinRequest::STATUS_PENDING, WineryJoinRequest::STATUS_APPROVED])
            ->exists();

        if ($existing) {
            $this->toastError(__('Ya tienes una solicitud activa con esta bodega.'));

            return;
        }

        $this->validate([
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        WineryJoinRequest::updateOrCreate(
            ['viticulturist_id' => $viticulturistId, 'winery_id' => $wineryId],
            [
                'status' => WineryJoinRequest::STATUS_PENDING,
                'message' => $this->message ?: null,
                'requested_at' => now(),
                'responded_at' => null,
            ]
        );

        $winery->notify(new WineryJoinRequestedNotification(Auth::user()));

        $this->showSearch = false;
        $this->searchQuery = '';
        $this->message = '';

        $this->toastSuccess(__('Solicitud enviada a ').$winery->name.'.');
    }

    public function cancelRequest(int $requestId): void
    {
        WineryJoinRequest::where('id', $requestId)
            ->where('viticulturist_id', Auth::id())
            ->where('status', WineryJoinRequest::STATUS_PENDING)
            ->firstOrFail()
            ->delete();

        $this->toastSuccess(__('Solicitud cancelada.'));
    }

    #[Layout('layouts.app', [
        'title' => 'Solicitudes de Bodega - Agro365',
        'description' => 'Solicita vincularte a una bodega para colaborar en la gestión de tus vendimias.',
    ])]
    public function render()
    {
        $viticulturistId = Auth::id();

        $pending = WineryJoinRequest::with('winery')
            ->where('viticulturist_id', $viticulturistId)
            ->where('status', WineryJoinRequest::STATUS_PENDING)
            ->orderByDesc('requested_at')
            ->get();

        $approved = WineryJoinRequest::with('winery')
            ->where('viticulturist_id', $viticulturistId)
            ->where('status', WineryJoinRequest::STATUS_APPROVED)
            ->orderByDesc('responded_at')
            ->get();

        $rejected = WineryJoinRequest::with('winery')
            ->where('viticulturist_id', $viticulturistId)
            ->where('status', WineryJoinRequest::STATUS_REJECTED)
            ->orderByDesc('responded_at')
            ->get();

        $wineries = $this->showSearch ? $this->searchWineries() : [];

        return view('livewire.viticulturist.winery-requests.index', [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'wineries' => $wineries,
        ]);
    }
}
