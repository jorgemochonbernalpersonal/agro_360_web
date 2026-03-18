<?php

namespace App\Livewire\Winery\Denomination\Requests;

use App\Models\SupervisorRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    // ── Responder ─────────────────────────────────────────────────────────────
    public ?int   $respondingId    = null;
    public string $responseNotes   = '';

    protected $queryString = [
        'statusFilter' => ['except' => ''],
    ];

    protected function rules(): array
    {
        return [
            'responseNotes' => 'nullable|string|max:2000',
        ];
    }

    public function updatingStatusFilter(): void { $this->resetPage(); }

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
        $this->validate();

        $request = SupervisorRequest::forWinery(Auth::id())
            ->where('status', SupervisorRequest::STATUS_PENDING)
            ->findOrFail($this->respondingId);

        $request->respond($this->responseNotes);

        $this->respondingId  = null;
        $this->responseNotes = '';

        $this->dispatch('toast', message: 'Respuesta enviada a la denominación.', type: 'success');
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
        $pendingCount = SupervisorRequest::forWinery($wineryId)->where('status', 'pending')->count();

        return view('livewire.winery.denomination.requests.index', [
            'requests'     => $requests,
            'pendingCount' => $pendingCount,
            'typeLabels'   => SupervisorRequest::TYPE_LABELS,
            'statusLabels' => SupervisorRequest::STATUS_LABELS,
            'statusColors' => SupervisorRequest::STATUS_COLORS,
        ]);
    }
}
