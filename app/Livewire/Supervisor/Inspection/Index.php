<?php

namespace App\Livewire\Supervisor\Inspection;

use App\Models\DoInspection;
use App\Models\SupervisorWinery;
use App\Models\WineryViticulturist;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

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
        $this->validate([
            'subject_type'     => 'required|in:winery,viticulturist',
            'subject_id'       => 'required|integer|exists:users,id',
            'inspection_date'  => 'required|date',
            'notes'            => 'nullable|string',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $doId = Auth::id();

        // Authorization: subject must be supervised by this DO
        $wineryIds = SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id');
        $vitIds    = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->pluck('viticulturist_id');

        $allowed = $this->subject_type === 'winery'
            ? $wineryIds->contains((int) $this->subject_id)
            : $vitIds->contains((int) $this->subject_id);

        if (!$allowed) {
            $this->toastError('El sujeto seleccionado no pertenece a esta denominación.');
            return;
        }

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

    public function updateStatus(int $inspectionId, string $status): void
    {
        $inspection = DoInspection::forSupervisor(Auth::id())->findOrFail($inspectionId);
        $inspection->update(['status' => $status]);
        $this->toastSuccess('Estado actualizado.');
    }

    public function delete(int $inspectionId): void
    {
        $inspection = DoInspection::forSupervisor(Auth::id())->findOrFail($inspectionId);
        $inspection->delete();
        $this->toastSuccess('Inspección eliminada.');
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

        $counts = [
            'all'         => DoInspection::forSupervisor($doId)->count(),
            'scheduled'   => DoInspection::forSupervisor($doId)->where('status', 'scheduled')->count(),
            'in_progress' => DoInspection::forSupervisor($doId)->where('status', 'in_progress')->count(),
            'completed'   => DoInspection::forSupervisor($doId)->where('status', 'completed')->count(),
        ];

        // Subjects for the create form
        $wineries        = \App\Models\User::whereIn('id', SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id'))
            ->orderBy('name')->get(['id', 'name']);
        $viticulturists  = \App\Models\User::whereIn('id',
                WineryViticulturist::where('supervisor_id', $doId)
                    ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
                    ->pluck('viticulturist_id')->unique()
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
