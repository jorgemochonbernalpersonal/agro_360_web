<?php

namespace App\Livewire\Supervisor\Requests;

use App\Models\SupervisorRequest;
use App\Models\SupervisorWinery;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $typeFilter   = '';
    public string $search       = '';

    // ── Formulario crear solicitud ────────────────────────────────────────────
    public bool   $showCreateModal = false;
    public string $formWineryId    = '';
    public string $formType        = '';
    public string $formTitle       = '';
    public string $formNotes       = '';

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'typeFilter'   => ['except' => ''],
        'search'       => ['except' => ''],
    ];

    protected function rules(): array
    {
        return [
            'formWineryId' => 'required|integer',
            'formType'     => 'required|in:' . implode(',', array_keys(SupervisorRequest::TYPE_LABELS)),
            'formTitle'    => 'nullable|string|max:255',
            'formNotes'    => 'nullable|string',
        ];
    }

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void   { $this->resetPage(); }

    public function openCreateModal(): void
    {
        $this->reset(['formWineryId', 'formType', 'formTitle', 'formNotes']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function create(): void
    {
        $this->validate();

        // Guard: la bodega debe estar adscrita a este supervisor
        SupervisorWinery::where('supervisor_id', Auth::id())
            ->where('winery_id', $this->formWineryId)
            ->firstOrFail();

        SupervisorRequest::create([
            'supervisor_id' => Auth::id(),
            'winery_id'     => $this->formWineryId,
            'type'          => $this->formType,
            'title'         => $this->formTitle ?: null,
            'notes'         => $this->formNotes ?: null,
            'status'        => SupervisorRequest::STATUS_DRAFT,
        ]);

        $this->showCreateModal = false;
        $this->dispatch('toast', message: 'Solicitud creada como borrador.', type: 'success');
    }

    public function send(int $requestId): void
    {
        $request = SupervisorRequest::forSupervisor(Auth::id())->findOrFail($requestId);
        $request->send();
        $this->dispatch('toast', message: 'Solicitud enviada a la bodega.', type: 'success');
    }

    public function approve(int $requestId): void
    {
        $request = SupervisorRequest::forSupervisor(Auth::id())->findOrFail($requestId);
        $request->approve();
        $this->dispatch('toast', message: 'Solicitud aprobada.', type: 'success');
    }

    public function reject(int $requestId): void
    {
        $request = SupervisorRequest::forSupervisor(Auth::id())->findOrFail($requestId);
        $request->reject();
        $this->dispatch('toast', message: 'Solicitud rechazada.', type: 'warning');
    }

    public function archive(int $requestId): void
    {
        $request = SupervisorRequest::forSupervisor(Auth::id())->findOrFail($requestId);
        $request->archive();
        $this->dispatch('toast', message: 'Solicitud archivada.', type: 'success');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $supervisorId = Auth::id();

        $query = SupervisorRequest::forSupervisor($supervisorId)->with('winery');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }
        if ($this->search) {
            $s = '%' . strtolower($this->search) . '%';
            $query->whereHas('winery', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', [$s]));
        }

        $requests = $query->orderByDesc('created_at')->paginate(15);

        $pendingCount   = SupervisorRequest::forSupervisor($supervisorId)->where('status', 'pending')->count();
        $inReviewCount  = SupervisorRequest::forSupervisor($supervisorId)->where('status', 'in_review')->count();

        $wineries = User::whereIn(
            'id',
            SupervisorWinery::where('supervisor_id', $supervisorId)->pluck('winery_id')
        )->orderBy('name')->get(['id', 'name']);

        return view('livewire.supervisor.requests.index', [
            'requests'     => $requests,
            'pendingCount' => $pendingCount,
            'inReviewCount'=> $inReviewCount,
            'wineries'     => $wineries,
            'typeLabels'   => SupervisorRequest::TYPE_LABELS,
            'statusLabels' => SupervisorRequest::STATUS_LABELS,
            'statusColors' => SupervisorRequest::STATUS_COLORS,
        ]);
    }
}
