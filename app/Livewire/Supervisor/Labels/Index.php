<?php

namespace App\Livewire\Supervisor\Labels;

use App\Models\DoLabel;
use App\Models\SupervisorWinery;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $currentTab    = 'all';
    public string $vintageFilter = '';
    public bool   $showCreate    = false;

    // Create form
    public string $winery_id          = '';
    public string $vintage            = '';
    public string $batch_number       = '';
    public int    $quantity_requested = 0;
    public string $notes              = '';
    public ?int   $fromRequestId      = null;   // vinculación con SupervisorRequest

    protected $queryString = [
        'currentTab'    => ['except' => 'all', 'as' => 'tab'],
        'vintageFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->vintage = (string) now()->year;

        if (request()->filled('from_winery')) {
            $this->winery_id  = (string) request()->integer('from_winery');
            $this->showCreate = true;
        }

        if (request()->filled('from_request')) {
            $this->fromRequestId = request()->integer('from_request');
        }
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function toggleCreate(): void
    {
        $this->showCreate = !$this->showCreate;
    }

    public function saveLabel(): void
    {
        Gate::authorize('create', DoLabel::class);

        if (!RateLimiter::attempt('label-save:' . Auth::id(), 20, fn() => null, 60)) {
            $this->toastError('Demasiadas solicitudes. Espera un minuto antes de continuar.');
            return;
        }

        $this->validate([
            'winery_id'          => 'required|integer|exists:users,id',
            'vintage'            => 'required|integer|min:1990|max:2100',
            'batch_number'       => 'nullable|string|max:100',
            'quantity_requested' => 'required|integer|min:1',
            'notes'              => 'nullable|string',
        ]);

        $doId = Auth::id();

        $isLinked = SupervisorWinery::where('supervisor_id', $doId)
            ->where('winery_id', $this->winery_id)
            ->exists();

        if (!$isLinked) {
            $this->toastError('La bodega seleccionada no pertenece a esta denominación.');
            return;
        }

        DoLabel::create([
            'supervisor_id'        => $doId,
            'winery_id'            => $this->winery_id,
            'supervisor_request_id'=> $this->fromRequestId,
            'vintage'              => $this->vintage,
            'batch_number'         => $this->batch_number ?: null,
            'quantity_requested'   => $this->quantity_requested,
            'notes'                => $this->notes ?: null,
            'requested_at'         => now(),
        ]);

        $this->reset(['batch_number', 'quantity_requested', 'notes', 'fromRequestId']);
        $this->showCreate = false;
        $this->toastSuccess('Solicitud de contraetiquetas registrada.');
    }

    public function issue(int $labelId): void
    {
        $label = DoLabel::forSupervisor(Auth::id())->findOrFail($labelId);
        Gate::authorize('update', $label);

        if (!in_array($label->status, [DoLabel::STATUS_PENDING, DoLabel::STATUS_APPROVED])) {
            $this->toastError('Solo se pueden emitir solicitudes pendientes o aprobadas.');
            return;
        }

        $label->update([
            'status'            => DoLabel::STATUS_ISSUED,
            'quantity_issued'   => $label->quantity_requested,
            'quantity_stock'    => $label->quantity_requested,
            'issued_at'         => now(),
            'issued_by'         => Auth::id(),
        ]);

        $this->toastSuccess('Contraetiquetas emitidas correctamente.');
    }

    public function approve(int $labelId): void
    {
        $label = DoLabel::forSupervisor(Auth::id())->findOrFail($labelId);
        Gate::authorize('update', $label);
        $label->update(['status' => DoLabel::STATUS_APPROVED]);
        $this->toastSuccess('Solicitud aprobada.');
    }

    public function cancel(int $labelId): void
    {
        $label = DoLabel::forSupervisor(Auth::id())->findOrFail($labelId);
        Gate::authorize('update', $label);
        $label->update(['status' => DoLabel::STATUS_CANCELLED]);
        $this->toastSuccess('Solicitud cancelada.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $query = DoLabel::forSupervisor($doId)->with(['winery:id,name']);

        if ($this->currentTab !== 'all') {
            $query->where('status', $this->currentTab);
        }

        if ($this->vintageFilter) {
            $query->where('vintage', $this->vintageFilter);
        }

        $labels = $query->orderByDesc('created_at')->paginate(15);

        $statusCounts = DoLabel::forSupervisor($doId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'all'      => $statusCounts->sum(),
            'pending'  => $statusCounts->get('pending', 0),
            'approved' => $statusCounts->get('approved', 0),
            'issued'   => $statusCounts->get('issued', 0),
        ];

        $availableVintages = DoLabel::forSupervisor($doId)
            ->select('vintage')->distinct()->orderByDesc('vintage')->pluck('vintage');

        $wineries = \App\Models\User::whereIn('id',
            SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id')
        )->orderBy('name')->get(['id', 'name']);

        $totalIssued = DoLabel::forSupervisor($doId)
            ->where('status', DoLabel::STATUS_ISSUED)
            ->when($this->vintageFilter, fn($q) => $q->where('vintage', $this->vintageFilter))
            ->sum('quantity_issued');

        $tabs = [
            'all'      => ['label' => 'Todas',      'count' => $counts['all']],
            'pending'  => ['label' => 'Pendientes', 'count' => $counts['pending']],
            'approved' => ['label' => 'Aprobadas',  'count' => $counts['approved']],
            'issued'   => ['label' => 'Emitidas',   'count' => $counts['issued']],
        ];

        return view('livewire.supervisor.labels.index', [
            'labels'            => $labels,
            'tabs'              => $tabs,
            'wineries'          => $wineries,
            'availableVintages' => $availableVintages,
            'totalIssued'       => $totalIssued,
        ]);
    }
}
