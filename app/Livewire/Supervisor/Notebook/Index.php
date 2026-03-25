<?php

namespace App\Livewire\Supervisor\Notebook;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Notifications\NotebookAccessRequestedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $filterStatus = 'all';
    public string $search       = '';

    public bool  $showRequestModal      = false;
    public ?int  $targetViticulturistId = null;

    protected $queryString = ['filterStatus', 'search'];

    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingSearch(): void        { $this->resetPage(); }

    public function openRequestModal(): void
    {
        $this->reset(['targetViticulturistId']);
        $this->showRequestModal = true;
    }

    public function closeRequestModal(): void
    {
        $this->showRequestModal = false;
    }

    public function requestAccess(): void
    {
        $this->validate(['targetViticulturistId' => 'required|exists:users,id']);

        $doId = Auth::id();

        // Guard: viticulturist must be in supervisor's pool
        SupervisorViticulturist::where('supervisor_id', $doId)
            ->where('viticulturist_id', $this->targetViticulturistId)
            ->firstOrFail();

        $existing = NotebookAccessRequest::where('supervisor_id', $doId)
            ->where('viticulturist_id', $this->targetViticulturistId)
            ->first();

        if ($existing && in_array($existing->status, [
            NotebookAccessRequest::STATUS_PENDING,
            NotebookAccessRequest::STATUS_APPROVED,
        ])) {
            $this->toastError('Ya existe una solicitud activa o aprobada para este viticultor.');
            return;
        }

        NotebookAccessRequest::updateOrCreate(
            [
                'supervisor_id'    => $doId,
                'viticulturist_id' => $this->targetViticulturistId,
            ],
            [
                'winery_id'    => null,
                'status'       => NotebookAccessRequest::STATUS_PENDING,
                'requested_at' => now(),
                'responded_at' => null,
            ]
        );

        $viticulturist = User::find($this->targetViticulturistId);
        $viticulturist?->notify(new NotebookAccessRequestedNotification(Auth::user()));

        Cache::forget("nav_badge_notebook_access_{$this->targetViticulturistId}");

        $this->closeRequestModal();
        $this->toastSuccess('Solicitud de acceso al cuaderno enviada.');
    }

    public function revokeAccess(int $id): void
    {
        $request = NotebookAccessRequest::where('id', $id)
            ->where('supervisor_id', Auth::id())
            ->firstOrFail();

        // Also revoke the cuaderno_access flag on the relation
        $relation = \App\Models\SupervisorViticulturist::where('supervisor_id', Auth::id())
            ->where('viticulturist_id', $request->viticulturist_id)
            ->first();

        $relation?->revokeNotebookAccess();

        $request->delete();
        $this->toastSuccess('Acceso al cuaderno revocado.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        // Only viticulturists who have logged in (can grant access)
        $poolIds = SupervisorViticulturist::where('supervisor_id', $doId)
            ->pluck('viticulturist_id');

        $activeVitIds = User::whereIn('id', $poolIds)
            ->where('can_login', true)
            ->pluck('id');

        $query = NotebookAccessRequest::where('supervisor_id', $doId)
            ->with(['viticulturist:id,name,email']);

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->whereHas('viticulturist', fn($q) => $q
                ->whereRaw('LOWER(name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(email) LIKE ?', [$term])
            );
        }

        $requests = $query->orderByDesc('requested_at')->paginate(20);

        $stats = [
            'total'    => NotebookAccessRequest::where('supervisor_id', $doId)->count(),
            'pending'  => NotebookAccessRequest::where('supervisor_id', $doId)->pending()->count(),
            'approved' => NotebookAccessRequest::where('supervisor_id', $doId)->approved()->count(),
            'rejected' => NotebookAccessRequest::where('supervisor_id', $doId)
                ->where('status', NotebookAccessRequest::STATUS_REJECTED)->count(),
        ];

        $alreadyRequested = NotebookAccessRequest::where('supervisor_id', $doId)
            ->whereIn('status', [NotebookAccessRequest::STATUS_PENDING, NotebookAccessRequest::STATUS_APPROVED])
            ->pluck('viticulturist_id');

        // Only active (can_login=true) viticultors can approve requests
        $availableForRequest = $this->showRequestModal
            ? User::whereIn('id', $activeVitIds)
                ->whereNotIn('id', $alreadyRequested)
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
            : collect();

        return view('livewire.supervisor.notebook.index', [
            'requests'            => $requests,
            'stats'               => $stats,
            'availableForRequest' => $availableForRequest,
        ]);
    }
}
