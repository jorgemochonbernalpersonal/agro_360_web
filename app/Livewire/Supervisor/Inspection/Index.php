<?php

namespace App\Livewire\Supervisor\Inspection;

use App\Models\DoInspection;
use App\Models\SupervisorRequest;
use App\Models\SupervisorWinery;
use App\Models\SupervisorViticulturist;
use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithOwnershipRules, WithToastNotifications;

    public string $currentTab  = 'all';
    public string $search      = '';
    public string $typeFilter  = '';
    public bool   $showCreate  = false;

    // New inspection form
    public string $subject_type     = 'winery';
    public string $subject_id       = '';
    public string $inspection_date  = '';
    public string $notes            = '';
    public string $reference_number = '';

    // Edit modal
    public bool   $showEdit              = false;
    public ?int   $editInspectionId      = null;
    public string $editInspectionDate    = '';
    public string $editNotes             = '';
    public string $editFindings          = '';
    public string $editResult            = '';
    public string $editReferenceNumber   = '';

    protected $queryString = [
        'currentTab' => ['except' => 'all', 'as' => 'tab'],
        'search'     => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->inspection_date = now()->format('Y-m-d');
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    public function toggleCreate(): void
    {
        $this->showCreate = !$this->showCreate;
    }

    public function saveInspection(): void
    {
        Gate::authorize('create', DoInspection::class);

        if (!RateLimiter::attempt('inspection-save:' . Auth::id(), 20, fn() => null, 60)) {
            $this->toastError('Demasiadas solicitudes. Espera un minuto antes de continuar.');
            return;
        }

        $this->validate([
            'subject_type'     => 'required|in:winery,viticulturist',
            'subject_id'       => $this->supervisorLinkedSubjectRule($this->subject_type),
            'inspection_date'  => 'required|date',
            'notes'            => 'nullable|string',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $doId = Auth::id();

        DoInspection::create([
            'supervisor_id'    => $doId,
            'subject_type'     => $this->subject_type,
            'subject_id'       => $this->subject_id,
            'inspection_date'  => $this->inspection_date,
            'notes'            => $this->notes ?: null,
            'reference_number' => $this->reference_number ?: null,
        ]);

        $this->reset(['subject_id', 'notes', 'reference_number']);
        $this->showCreate = false;
        $this->toastSuccess('Inspección programada correctamente.');
    }

    public function openEdit(int $inspectionId): void
    {
        $inspection = DoInspection::forSupervisor(Auth::id())->findOrFail($inspectionId);
        Gate::authorize('update', $inspection);

        $this->editInspectionId    = $inspectionId;
        $this->editInspectionDate  = $inspection->inspection_date->format('Y-m-d');
        $this->editNotes           = $inspection->notes           ?? '';
        $this->editFindings        = $inspection->findings        ?? '';
        $this->editResult          = $inspection->result          ?? '';
        $this->editReferenceNumber = $inspection->reference_number ?? '';
        $this->showEdit            = true;
        $this->resetValidation();
    }

    public function closeEdit(): void
    {
        $this->showEdit          = false;
        $this->editInspectionId  = null;
        $this->resetValidation();
    }

    public function updateInspection(): void
    {
        $inspection = DoInspection::forSupervisor(Auth::id())->findOrFail($this->editInspectionId);
        Gate::authorize('update', $inspection);

        $this->validate([
            'editInspectionDate'  => 'required|date',
            'editResult'          => 'nullable|in:compliant,non_compliant,pending',
            'editFindings'        => 'nullable|string',
            'editNotes'           => 'nullable|string',
            'editReferenceNumber' => 'nullable|string|max:100',
        ]);

        $inspection->update([
            'inspection_date'  => $this->editInspectionDate,
            'result'           => $this->editResult           ?: null,
            'findings'         => $this->editFindings         ?: null,
            'notes'            => $this->editNotes            ?: null,
            'reference_number' => $this->editReferenceNumber  ?: null,
        ]);

        $this->closeEdit();
        $this->toastSuccess('Inspección actualizada.');
    }

    public function updateStatus(int $inspectionId, string $status): void
    {
        $inspection = DoInspection::forSupervisor(Auth::id())->findOrFail($inspectionId);
        Gate::authorize('update', $inspection);
        $inspection->update(['status' => $status]);
        $this->toastSuccess('Estado actualizado.');
    }

    public function delete(int $inspectionId): void
    {
        $inspection = DoInspection::forSupervisor(Auth::id())->findOrFail($inspectionId);
        Gate::authorize('delete', $inspection);
        $inspection->delete();
        $this->toastSuccess('Inspección eliminada.');
    }

    public function createNonconformityFromInspection(int $inspectionId): void
    {
        $inspection = DoInspection::forSupervisor(Auth::id())
            ->where('subject_type', 'winery')
            ->where('result', DoInspection::RESULT_NON_COMPLIANT)
            ->findOrFail($inspectionId);
        Gate::authorize('update', $inspection);

        SupervisorRequest::create([
            'supervisor_id' => Auth::id(),
            'winery_id'     => $inspection->subject_id,
            'type'          => SupervisorRequest::TYPE_NONCONFORMITY,
            'status'        => SupervisorRequest::STATUS_DRAFT,
            'title'         => 'No conformidad — ' . ($inspection->reference_number ?? $inspection->inspection_date->format('d/m/Y')),
            'notes'         => $inspection->findings ?: null,
        ]);

        $this->redirect(route('supervisor.requests.index'));
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $query = DoInspection::forSupervisor($doId)->with(['subject']);

        if ($this->currentTab !== 'all') {
            $query->where('status', $this->currentTab);
        }

        if ($this->typeFilter) {
            $query->where('subject_type', $this->typeFilter);
        }

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->whereHas('subject', function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term]);
            });
        }

        $inspections = $query->orderByDesc('inspection_date')->paginate(15);

        $statusCounts = DoInspection::forSupervisor($doId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'all'         => $statusCounts->sum(),
            'scheduled'   => $statusCounts->get('scheduled', 0),
            'in_progress' => $statusCounts->get('in_progress', 0),
            'completed'   => $statusCounts->get('completed', 0),
        ];

        // Subjects for the create form
        $wineries        = \App\Models\User::whereIn('id', SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id'))
            ->orderBy('name')->get(['id', 'name']);
        $viticulturists  = \App\Models\User::whereIn('id',
                SupervisorViticulturist::where('supervisor_id', $doId)->pluck('viticulturist_id')
            )->orderBy('name')->get(['id', 'name']);

        $tabs = [
            'all'         => ['label' => 'Todas',       'count' => $counts['all']],
            'scheduled'   => ['label' => 'Programadas', 'count' => $counts['scheduled']],
            'in_progress' => ['label' => 'En curso',    'count' => $counts['in_progress']],
            'completed'   => ['label' => 'Completadas', 'count' => $counts['completed']],
        ];

        return view('livewire.supervisor.inspection.index', [
            'inspections'    => $inspections,
            'tabs'           => $tabs,
            'wineries'       => $wineries,
            'viticulturists' => $viticulturists,
        ]);
    }
}
