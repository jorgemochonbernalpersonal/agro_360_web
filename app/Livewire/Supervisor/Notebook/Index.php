<?php

namespace App\Livewire\Supervisor\Notebook;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\NotebookAccessRequest;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $filterStatus = 'all';
    public string $search       = '';

    public bool  $showRequestModal       = false;
    public ?int  $targetViticulturistId  = null;

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

        $existing = NotebookAccessRequest::where('winery_id', $doId)
            ->where('viticulturist_id', $this->targetViticulturistId)
            ->first();

        if ($existing && in_array($existing->status, [
            NotebookAccessRequest::STATUS_PENDING,
            NotebookAccessRequest::STATUS_APPROVED,
        ])) {
            $this->toastError('Ya existe una solicitud activa o aprobada para este viticultor.');
            return;
        }

        // Reuse existing rejected record or create new one (unique constraint on winery+viticulturist)
        NotebookAccessRequest::updateOrCreate(
            ['winery_id' => $doId, 'viticulturist_id' => $this->targetViticulturistId],
            ['status' => NotebookAccessRequest::STATUS_PENDING, 'requested_at' => now(), 'responded_at' => null]
        );

        $this->closeRequestModal();
        $this->toastSuccess('Solicitud de acceso al cuaderno enviada.');
    }

    public function revokeAccess(int $id): void
    {
        $request = NotebookAccessRequest::where('id', $id)
            ->where('winery_id', Auth::id())
            ->firstOrFail();

        $request->delete();
        $this->toastSuccess('Acceso al cuaderno revocado.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $viticulturistIds = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->pluck('viticulturist_id')
            ->unique();

        $query = NotebookAccessRequest::where('winery_id', $doId)
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
            'total'    => NotebookAccessRequest::where('winery_id', $doId)->count(),
            'pending'  => NotebookAccessRequest::where('winery_id', $doId)->pending()->count(),
            'approved' => NotebookAccessRequest::where('winery_id', $doId)->approved()->count(),
            'rejected' => NotebookAccessRequest::where('winery_id', $doId)
                ->where('status', NotebookAccessRequest::STATUS_REJECTED)->count(),
        ];

        $alreadyRequested = NotebookAccessRequest::where('winery_id', $doId)
            ->whereIn('status', [NotebookAccessRequest::STATUS_PENDING, NotebookAccessRequest::STATUS_APPROVED])
            ->pluck('viticulturist_id');

        $availableForRequest = $this->showRequestModal
            ? User::whereIn('id', $viticulturistIds)
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
