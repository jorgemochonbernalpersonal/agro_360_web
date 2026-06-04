<?php

namespace App\Livewire\Supervisor\Requests;

use App\Models\SupervisorRequest;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Notifications\SupervisorRequestResolvedNotification;
use App\Notifications\SupervisorRequestSentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public string $typeFilter = '';

    public string $search = '';

    // ── Selección para bulk actions ───────────────────────────────────────────
    public array $selected = [];

    // ── Formulario crear solicitud ────────────────────────────────────────────
    public bool $showCreateModal = false;

    public string $formWineryId = '';

    public string $formType = '';

    public string $formTitle = '';

    public string $formNotes = '';

    public string $formDueDate = '';

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['formWineryId', 'formType', 'formTitle', 'formNotes', 'formDueDate']);
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
            'winery_id' => $this->formWineryId,
            'type' => $this->formType,
            'title' => $this->formTitle ?: null,
            'notes' => $this->formNotes ?: null,
            'due_date' => $this->formDueDate ?: null,
            'status' => SupervisorRequest::STATUS_DRAFT,
        ]);

        $this->showCreateModal = false;
        $this->dispatch('toast', message: __('Solicitud creada como borrador.'), type: 'success');
    }

    public function send(int $requestId): void
    {
        $request = SupervisorRequest::forSupervisor(Auth::id())->with('winery')->findOrFail($requestId);
        $request->send();

        // Notificar a la bodega + invalidar badges
        $request->winery?->notify(new SupervisorRequestSentNotification($request));
        Cache::forget("winery:{$request->winery_id}:pending_do_requests");
        Cache::forget("supervisor:{$request->supervisor_id}:inbox_count");

        $this->dispatch('toast', message: __('Solicitud enviada a la bodega.'), type: 'success');
    }

    public function approve(int $requestId): void
    {
        $request = SupervisorRequest::forSupervisor(Auth::id())->with('winery', 'supervisor')->findOrFail($requestId);
        $request->approve();

        // Notificar a la bodega + invalidar badges
        $request->winery?->notify(new SupervisorRequestResolvedNotification($request, SupervisorRequest::STATUS_APPROVED));
        Cache::forget("winery:{$request->winery_id}:pending_do_requests");
        Cache::forget("supervisor:{$request->supervisor_id}:inbox_count");

        $this->dispatch('toast', message: __('Solicitud aprobada.'), type: 'success');
    }

    public function reject(int $requestId): void
    {
        $request = SupervisorRequest::forSupervisor(Auth::id())->with('winery', 'supervisor')->findOrFail($requestId);
        $request->reject();

        // Notificar a la bodega + invalidar badges
        $request->winery?->notify(new SupervisorRequestResolvedNotification($request, SupervisorRequest::STATUS_REJECTED));
        Cache::forget("winery:{$request->winery_id}:pending_do_requests");
        Cache::forget("supervisor:{$request->supervisor_id}:inbox_count");

        $this->dispatch('toast', message: __('Solicitud rechazada.'), type: 'warning');
    }

    public function archive(int $requestId): void
    {
        $request = SupervisorRequest::forSupervisor(Auth::id())->findOrFail($requestId);
        $request->archive();
        Cache::forget("winery:{$request->winery_id}:pending_do_requests");
        $this->dispatch('toast', message: __('Solicitud archivada.'), type: 'success');
    }

    public function bulkArchive(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = SupervisorRequest::forSupervisor(Auth::id())
            ->whereIn('id', $this->selected)
            ->whereIn('status', [SupervisorRequest::STATUS_APPROVED, SupervisorRequest::STATUS_REJECTED])
            ->get()
            ->each(function (SupervisorRequest $req) {
                $req->archive();
                Cache::forget("winery:{$req->winery_id}:pending_do_requests");
            })
            ->count();

        $this->selected = [];
        $this->dispatch('toast', message: __(':count solicitudes archivadas.', ['count' => $count]), type: 'success');
    }

    public function deleteDraft(int $requestId): void
    {
        $request = SupervisorRequest::forSupervisor(Auth::id())
            ->where('status', SupervisorRequest::STATUS_DRAFT)
            ->findOrFail($requestId);

        $request->delete();
        $this->dispatch('toast', message: __('Borrador eliminado.'), type: 'success');
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
            $s = '%'.strtolower($this->search).'%';
            $query->whereHas('winery', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', [$s]));
        }

        $requests = $query->orderByDesc('created_at')->paginate(15);

        $pendingCount = SupervisorRequest::forSupervisor($supervisorId)->where('status', 'pending')->count();
        $inReviewCount = SupervisorRequest::forSupervisor($supervisorId)->where('status', 'in_review')->count();

        $wineries = User::whereIn(
            'id',
            SupervisorWinery::where('supervisor_id', $supervisorId)->pluck('winery_id')
        )->orderBy('name')->get(['id', 'name']);

        return view('livewire.supervisor.requests.index', [
            'requests' => $requests,
            'pendingCount' => $pendingCount,
            'inReviewCount' => $inReviewCount,
            'wineries' => $wineries,
            'typeLabels' => SupervisorRequest::typeLabelOptions(),
            'statusLabels' => SupervisorRequest::statusLabelOptions(),
            'statusColors' => SupervisorRequest::STATUS_COLORS,
        ]);
    }

    protected function rules(): array
    {
        return [
            'formWineryId' => 'required|integer',
            'formType' => 'required|in:'.implode(',', array_keys(SupervisorRequest::TYPE_LABELS)),
            'formTitle' => 'nullable|string|max:255',
            'formNotes' => 'nullable|string',
            'formDueDate' => 'nullable|date|after:today',
        ];
    }
}
