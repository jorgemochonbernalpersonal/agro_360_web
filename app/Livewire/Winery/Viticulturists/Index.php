<?php

namespace App\Livewire\Winery\Viticulturists;

use App\Exports\ViticulturistExport;
use App\Livewire\Winery\AbstractIndex;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\WineryJoinRequest;
use App\Models\WineryViticulturist;
use App\Notifications\WineryJoinRespondedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class Index extends AbstractIndex
{
    public string $search       = '';
    public string $sourceFilter = '';
    public string $statusFilter = '';

    // ── D.O. pool modal ───────────────────────────────────────────────────────
    public bool   $showDOModal = false;
    public string $doSearch    = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'sourceFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingSourceFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'sourceFilter' => '', 'statusFilter' => ''];
    }

    protected function baseQuery(): Builder
    {
        return WineryViticulturist::where('winery_id', $this->wineryId())
            ->with(['viticulturist' => fn($q) => $q->withCount('plots')->select([
                'id', 'name', 'email', 'can_login', 'email_verified_at',
                'invitation_token', 'invitation_sent_at', 'invitation_expires_at',
            ])]);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $search = '%' . mb_strtolower($this->search) . '%';
            $query->whereHas('viticulturist', fn($q) =>
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$search])
            );
        }

        if ($this->sourceFilter) {
            $query->where('source', $this->sourceFilter);
        }

        if ($this->statusFilter === 'active') {
            $query->whereHas('viticulturist', fn($q) => $q->where('can_login', true));
        } elseif ($this->statusFilter === 'pending') {
            $query->whereHas('viticulturist', fn($q) => $q->where('can_login', false));
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['created_at', 'desc'];
    }

    // ── D.O. pool actions ─────────────────────────────────────────────────────

    public function openDOModal(): void
    {
        $this->doSearch    = '';
        $this->showDOModal = true;
    }

    public function closeDOModal(): void
    {
        $this->showDOModal = false;
    }

    public function assignFromDO(int $viticulturistId): void
    {
        $wineryId      = $this->wineryId();
        $supervisorIds = SupervisorWinery::where('winery_id', $wineryId)->pluck('supervisor_id');

        $sv = SupervisorViticulturist::where('viticulturist_id', $viticulturistId)
            ->whereIn('supervisor_id', $supervisorIds)
            ->firstOrFail();

        // Idempotent — no duplicate
        if (WineryViticulturist::where('winery_id', $wineryId)->where('viticulturist_id', $viticulturistId)->exists()) {
            $this->closeDOModal();
            return;
        }

        WineryViticulturist::create([
            'winery_id'        => $wineryId,
            'viticulturist_id' => $viticulturistId,
            'source'           => WineryViticulturist::SOURCE_SUPERVISOR,
            'supervisor_id'    => $sv->supervisor_id,
            'assigned_by'      => Auth::id(),
        ]);

        $this->closeDOModal();
        $this->toastSuccess(__('Viticultor asignado correctamente.'));
    }

    public function unassignFromDO(int $viticulturistId): void
    {
        WineryViticulturist::where('winery_id', $this->wineryId())
            ->where('viticulturist_id', $viticulturistId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->firstOrFail()
            ->delete();

        $this->toastSuccess(__('Viticultor desasignado.'));
    }

    public function unlinkViticulturist(int $viticulturistId): void
    {
        $relation = WineryViticulturist::where('winery_id', $this->wineryId())
            ->where('viticulturist_id', $viticulturistId)
            ->where('source', WineryViticulturist::SOURCE_OWN)
            ->firstOrFail();

        // Revocar acceso al cuaderno si estaba concedido
        if ($relation->notebook_access) {
            $relation->revokeNotebookAccess();
        }

        $relation->delete();

        $this->toastSuccess(__('Viticultor desvinculado correctamente.'));
    }

    // ── Join requests ────────────────────────────────────────────────────────

    public function approveJoinRequest(int $requestId): void
    {
        $wineryId = $this->wineryId();

        $joinRequest = WineryJoinRequest::where('id', $requestId)
            ->where('winery_id', $wineryId)
            ->where('status', WineryJoinRequest::STATUS_PENDING)
            ->firstOrFail();

        // Idempotent: skip if already linked
        if (!WineryViticulturist::where('winery_id', $wineryId)->where('viticulturist_id', $joinRequest->viticulturist_id)->exists()) {
            WineryViticulturist::create([
                'winery_id'        => $wineryId,
                'viticulturist_id' => $joinRequest->viticulturist_id,
                'source'           => WineryViticulturist::SOURCE_SELF,
                'assigned_by'      => Auth::id(),
            ]);
        }

        $joinRequest->update([
            'status'       => WineryJoinRequest::STATUS_APPROVED,
            'responded_at' => now(),
        ]);

        $joinRequest->viticulturist->notify(new WineryJoinRespondedNotification(Auth::user(), WineryJoinRequest::STATUS_APPROVED));

        $this->toastSuccess(__('Solicitud aprobada. Viticultor vinculado correctamente.'));
    }

    public function rejectJoinRequest(int $requestId): void
    {
        $joinRequest = WineryJoinRequest::where('id', $requestId)
            ->where('winery_id', $this->wineryId())
            ->where('status', WineryJoinRequest::STATUS_PENDING)
            ->firstOrFail();

        $joinRequest->update([
            'status'       => WineryJoinRequest::STATUS_REJECTED,
            'responded_at' => now(),
        ]);

        $joinRequest->viticulturist->notify(new WineryJoinRespondedNotification(Auth::user(), WineryJoinRequest::STATUS_REJECTED));

        $this->toastSuccess(__('Solicitud rechazada.'));
    }

    // ── Export ────────────────────────────────────────────────────────────────

    public function export()
    {
        return Excel::download(
            new ViticulturistExport($this->wineryId()),
            'viticultores_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    // ── View data ─────────────────────────────────────────────────────────────

    protected function viewData(mixed $entries): array
    {
        $wineryId      = $this->wineryId();
        $base          = WineryViticulturist::where('winery_id', $wineryId);
        $supervisorIds = SupervisorWinery::where('winery_id', $wineryId)->pluck('supervisor_id');

        $stats = [
            'total'         => (clone $base)->count(),
            'active'        => (clone $base)->whereHas('viticulturist', fn($q) => $q->where('can_login', true))->count(),
            'pending'       => (clone $base)->whereHas('viticulturist', fn($q) => $q->where('can_login', false))->count(),
            'own'           => (clone $base)->where('source', 'own')->count(),
            'supervisor'    => (clone $base)->where('source', 'supervisor')->count(),
            'with_notebook' => (clone $base)->where('notebook_access', true)->count(),
        ];

        // Pool: supervisor viticultors not yet assigned to this winery
        $doPool = collect();
        if ($this->showDOModal && $supervisorIds->isNotEmpty()) {
            $alreadyAssigned = WineryViticulturist::where('winery_id', $wineryId)
                ->pluck('viticulturist_id');

            $poolQuery = SupervisorViticulturist::whereIn('supervisor_id', $supervisorIds)
                ->whereNotIn('viticulturist_id', $alreadyAssigned)
                ->with([
                    'viticulturist:id,name,email,can_login',
                    'supervisor:id,name',
                ]);

            if ($this->doSearch) {
                $s = '%' . mb_strtolower($this->doSearch) . '%';
                $poolQuery->whereHas('viticulturist', fn($q) =>
                    $q->whereRaw('LOWER(name) LIKE ?', [$s])
                      ->orWhereRaw('LOWER(email) LIKE ?', [$s])
                );
            }

            $doPool = $poolQuery->get();
        }

        $joinRequests = WineryJoinRequest::with('viticulturist')
            ->where('winery_id', $wineryId)
            ->where('status', WineryJoinRequest::STATUS_PENDING)
            ->orderBy('requested_at')
            ->get();

        return [
            'relations'      => $entries,
            'stats'          => $stats,
            'hasSupervisors' => $supervisorIds->isNotEmpty(),
            'doPool'         => $doPool,
            'joinRequests'   => $joinRequests,
        ];
    }
}
