<?php

namespace App\Livewire\Supervisor\Regulation;

use App\Models\Certification;
use App\Models\DoDocument;
use App\Models\PlotPlanting;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $currentTab     = 'autorizaciones';
    public string $search         = '';
    public string $filterVit      = '';
    public string $filterStatus   = '';
    public string $filterRightType = '';

    // Document form (pliego / reglamento)
    public bool   $showDocForm    = false;
    public string $docFormType    = '';
    public ?int   $editDocId      = null;
    public string $docTitle       = '';
    public string $docVersion     = '';
    public string $docEffectiveDate = '';
    public string $docContent     = '';
    public string $docStatus      = 'draft';

    protected $queryString = [
        'currentTab'      => ['except' => 'autorizaciones', 'as' => 'tab'],
        'search'          => ['except' => ''],
        'filterVit'       => ['except' => ''],
        'filterStatus'    => ['except' => ''],
        'filterRightType' => ['except' => ''],
    ];

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->search         = '';
        $this->filterVit      = '';
        $this->filterStatus   = '';
        $this->filterRightType = '';
        $this->resetPage();
    }

    public function updatingSearch(): void          { $this->resetPage(); }
    public function updatingFilterVit(): void       { $this->resetPage(); }
    public function updatingFilterStatus(): void    { $this->resetPage(); }
    public function updatingFilterRightType(): void { $this->resetPage(); }

    // ── Document CRUD ─────────────────────────────────────────────────────────

    public function openDocForm(string $type, ?int $docId = null): void
    {
        $this->docFormType  = $type;
        $this->editDocId    = $docId;
        $this->docTitle     = '';
        $this->docVersion   = '';
        $this->docEffectiveDate = '';
        $this->docContent   = '';
        $this->docStatus    = 'draft';

        if ($docId) {
            $doc = DoDocument::forSupervisor(Auth::id())->findOrFail($docId);
            $this->docTitle         = $doc->title;
            $this->docVersion       = $doc->version          ?? '';
            $this->docEffectiveDate = $doc->effective_date   ? $doc->effective_date->format('Y-m-d') : '';
            $this->docContent       = $doc->content          ?? '';
            $this->docStatus        = $doc->status;
        }

        $this->showDocForm = true;
        $this->resetValidation();
    }

    public function closeDocForm(): void
    {
        $this->showDocForm = false;
        $this->editDocId   = null;
        $this->resetValidation();
    }

    public function saveDocument(): void
    {
        $this->validate([
            'docTitle'          => 'required|string|max:255',
            'docVersion'        => 'nullable|string|max:30',
            'docEffectiveDate'  => 'nullable|date',
            'docContent'        => 'nullable|string',
            'docStatus'         => 'required|in:draft,active,archived',
        ]);

        $data = [
            'supervisor_id'  => Auth::id(),
            'type'           => $this->docFormType,
            'title'          => $this->docTitle,
            'version'        => $this->docVersion         ?: null,
            'effective_date' => $this->docEffectiveDate   ?: null,
            'content'        => $this->docContent         ?: null,
            'status'         => $this->docStatus,
        ];

        if ($this->editDocId) {
            DoDocument::forSupervisor(Auth::id())->findOrFail($this->editDocId)->update($data);
            $this->toastSuccess('Documento actualizado.');
        } else {
            DoDocument::create($data);
            $this->toastSuccess('Documento creado.');
        }

        $this->closeDocForm();
    }

    public function archiveDocument(int $docId): void
    {
        DoDocument::forSupervisor(Auth::id())->findOrFail($docId)->update(['status' => DoDocument::STATUS_ARCHIVED]);
        $this->toastSuccess('Documento archivado.');
    }

    public function activateDocument(int $docId): void
    {
        DoDocument::forSupervisor(Auth::id())->findOrFail($docId)->update(['status' => DoDocument::STATUS_ACTIVE]);
        $this->toastSuccess('Documento marcado como vigente.');
    }

    public function deleteDocument(int $docId): void
    {
        DoDocument::forSupervisor(Auth::id())->findOrFail($docId)->delete();
        $this->toastSuccess('Documento eliminado.');
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $viticulturistIds = SupervisorViticulturist::where('supervisor_id', $doId)
            ->pluck('viticulturist_id');

        $viticulturists = User::whereIn('id', $viticulturistIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $data = match ($this->currentTab) {
            'autorizaciones'  => $this->getAutorizacionesData($viticulturistIds),
            'certificaciones' => $this->getCertificacionesData($viticulturistIds),
            'pliego'          => $this->getDocumentData($doId, DoDocument::TYPE_PLIEGO),
            'reglamento'      => $this->getDocumentData($doId, DoDocument::TYPE_REGLAMENTO),
            default           => [],
        };

        $tabs = [
            'autorizaciones'  => ['label' => 'Autorizaciones de plantación'],
            'certificaciones' => ['label' => 'Certificaciones ecológicas'],
            'pliego'          => ['label' => 'Pliego de condiciones'],
            'reglamento'      => ['label' => 'Reglamento interno'],
        ];

        return view('livewire.supervisor.regulation.index', array_merge($data, [
            'tabs'           => $tabs,
            'viticulturists' => $viticulturists,
        ]));
    }

    private function getAutorizacionesData($viticulturistIds): array
    {
        $plotIds = \App\Models\Plot::whereIn('viticulturist_id', $viticulturistIds)->pluck('id');

        $query = PlotPlanting::whereIn('plot_id', $plotIds)
            ->whereNotNull('planting_authorization')
            ->with(['plot.viticulturist:id,name', 'grapeVariety:id,name']);

        if ($this->filterVit) {
            $filteredPlotIds = \App\Models\Plot::where('viticulturist_id', $this->filterVit)->pluck('id');
            $query->whereIn('plot_id', $filteredPlotIds);
        }

        if ($this->filterRightType) {
            $query->where('right_type', $this->filterRightType);
        }

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(planting_authorization) LIKE ?', [$term])
                  ->orWhereHas('plot', fn($p) => $p->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        $items = $query->orderByDesc('authorization_date')->paginate(15);

        $allQuery = PlotPlanting::whereIn('plot_id', $plotIds)->whereNotNull('planting_authorization');
        $stats = [
            'total'        => $allQuery->count(),
            'nueva'        => (clone $allQuery)->where('right_type', 'nueva')->count(),
            'replantacion' => (clone $allQuery)->where('right_type', 'replantacion')->count(),
            'conversion'   => (clone $allQuery)->where('right_type', 'conversion')->count(),
            'transferencia'=> (clone $allQuery)->where('right_type', 'transferencia')->count(),
        ];

        return ['items' => $items, 'stats' => $stats];
    }

    private function getCertificacionesData($viticulturistIds): array
    {
        $query = Certification::whereIn('viticulturist_id', $viticulturistIds)
            ->where('certification_type', 'ecologico')
            ->with('viticulturist:id,name');

        if ($this->filterVit) {
            $query->where('viticulturist_id', $this->filterVit);
        }

        if ($this->filterStatus === 'active') {
            $query->where('active', true)->where(fn($q) =>
                $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', now())
            );
        } elseif ($this->filterStatus === 'expiring') {
            $query->where('active', true)
                  ->whereDate('expiry_date', '>=', now())
                  ->whereDate('expiry_date', '<=', now()->addDays(60));
        } elseif ($this->filterStatus === 'expired') {
            $query->where('active', true)->whereDate('expiry_date', '<', now());
        }

        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(certificate_number) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(certifying_body) LIKE ?', [$term])
                  ->orWhereHas('viticulturist', fn($v) => $v->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        $items = $query->orderBy('expiry_date')->paginate(15);

        $all   = Certification::whereIn('viticulturist_id', $viticulturistIds)->where('certification_type', 'ecologico')->where('active', true)->get();
        $stats = [
            'total'    => $all->count(),
            'active'   => $all->filter(fn($c) => !$c->is_expired)->count(),
            'expiring' => $all->filter(fn($c) => $c->is_expiring_soon)->count(),
            'expired'  => $all->filter(fn($c) => $c->is_expired)->count(),
        ];

        return ['items' => $items, 'stats' => $stats];
    }

    private function getDocumentData(int $doId, string $type): array
    {
        $query = DoDocument::forSupervisor($doId)->ofType($type);

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $items = $query->orderByDesc('effective_date')->orderByDesc('created_at')->paginate(15);

        $stats = [
            'draft'    => DoDocument::forSupervisor($doId)->ofType($type)->where('status', 'draft')->count(),
            'active'   => DoDocument::forSupervisor($doId)->ofType($type)->where('status', 'active')->count(),
            'archived' => DoDocument::forSupervisor($doId)->ofType($type)->where('status', 'archived')->count(),
        ];

        return ['items' => $items, 'stats' => $stats];
    }
}
