<?php

namespace App\Livewire\Winery\Denomination\Requests;

use App\Models\SupervisorRequest;
use App\Models\SupervisorWinery;
use App\Notifications\SupervisorRequestReceivedNotification;
use App\Notifications\SupervisorRequestRespondedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    // ── Responder (supervisor → bodega) ───────────────────────────────────────
    public ?int   $respondingId  = null;
    public string $responseNotes = '';

    // ── Crear solicitud (bodega → supervisor) ─────────────────────────────────
    public bool   $showCreate  = false;
    public string $createType  = '';
    public string $createTitle = '';
    public string $createNotes = '';

    protected $queryString = [
        'statusFilter' => ['except' => ''],
    ];

    protected function rules(): array
    {
        return [
            'responseNotes' => 'nullable|string|max:2000',
            'createType'    => 'required|in:' . implode(',', SupervisorRequest::WINERY_INITIATED),
            'createTitle'   => 'required|string|max:255',
            'createNotes'   => 'nullable|string|max:2000',
        ];
    }

    public function updatingStatusFilter(): void { $this->resetPage(); }

    // ── Responder ─────────────────────────────────────────────────────────────

    public function startResponding(int $requestId): void
    {
        $this->respondingId  = $requestId;
        $this->responseNotes = '';
    }

    public function cancelResponding(): void
    {
        $this->respondingId  = null;
        $this->responseNotes = '';
    }

    public function respond(): void
    {
        $this->validateOnly('responseNotes');

        $request = SupervisorRequest::forWinery(Auth::id())
            ->where('status', SupervisorRequest::STATUS_PENDING)
            ->with('supervisor', 'winery')
            ->findOrFail($this->respondingId);

        $request->respond($this->responseNotes);

        // Notificar al supervisor + invalidar su badge
        $request->supervisor?->notify(new SupervisorRequestRespondedNotification($request));
        Cache::forget("supervisor:{$request->supervisor_id}:inbox_count");

        $this->respondingId  = null;
        $this->responseNotes = '';

        $this->dispatch('toast', message: 'Respuesta enviada a la denominación.', type: 'success');
    }

    // ── Retirar solicitud pendiente ───────────────────────────────────────────

    public function retractRequest(int $requestId): void
    {
        $request = SupervisorRequest::forWinery(Auth::id())
            ->where('status', SupervisorRequest::STATUS_PENDING)
            ->whereIn('type', SupervisorRequest::WINERY_INITIATED)
            ->findOrFail($requestId);

        $supervisorId = $request->supervisor_id;

        $request->archive();

        Cache::forget("winery:{$request->winery_id}:pending_do_requests");
        Cache::forget("supervisor:{$supervisorId}:inbox_count");

        $this->dispatch('toast', message: 'Solicitud retirada.', type: 'success');
    }

    // ── Crear solicitud ───────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->showCreate  = true;
        $this->createType  = '';
        $this->createTitle = '';
        $this->createNotes = '';
        $this->resetErrorBag();
    }

    public function closeCreate(): void
    {
        $this->showCreate = false;
        $this->resetErrorBag();
    }

    public function saveRequest(): void
    {
        $this->validateOnly('createType,createTitle,createNotes');

        $wineryId = Auth::id();

        $supervisorRelation = SupervisorWinery::where('winery_id', $wineryId)->first();

        if (! $supervisorRelation) {
            $this->dispatch('toast', message: 'No tienes una denominación de origen asignada.', type: 'error');
            return;
        }

        $newRequest = SupervisorRequest::create([
            'supervisor_id' => $supervisorRelation->supervisor_id,
            'winery_id'     => $wineryId,
            'type'          => $this->createType,
            'status'        => SupervisorRequest::STATUS_PENDING,
            'title'         => $this->createTitle,
            'notes'         => $this->createNotes ?: null,
            'sent_at'       => now(),
        ]);

        Cache::forget("winery:{$wineryId}:pending_do_requests");
        Cache::forget("supervisor:{$supervisorRelation->supervisor_id}:inbox_count");

        // Notificar al supervisor
        $supervisorRelation->load('supervisor');
        $supervisorRelation->supervisor?->notify(
            new SupervisorRequestReceivedNotification($newRequest->setRelation('winery', Auth::user()))
        );

        $this->showCreate = false;
        $this->resetPage();

        $this->dispatch('toast', message: 'Solicitud enviada a la denominación.', type: 'success');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $wineryId = Auth::id();

        $query = SupervisorRequest::forWinery($wineryId)->with('supervisor');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $requests     = $query->orderByDesc('created_at')->paginate(15);
        $pendingCount = SupervisorRequest::forWinery($wineryId)->where('status', SupervisorRequest::STATUS_PENDING)->count();

        $wineryInitiatedLabels = collect(SupervisorRequest::WINERY_INITIATED)
            ->mapWithKeys(fn ($type) => [$type => SupervisorRequest::TYPE_LABELS[$type]]);

        return view('livewire.winery.denomination.requests.index', [
            'requests'              => $requests,
            'pendingCount'          => $pendingCount,
            'typeLabels'            => SupervisorRequest::TYPE_LABELS,
            'statusLabels'          => SupervisorRequest::STATUS_LABELS,
            'statusColors'          => SupervisorRequest::STATUS_COLORS,
            'wineryInitiatedLabels' => $wineryInitiatedLabels,
        ]);
    }
}
