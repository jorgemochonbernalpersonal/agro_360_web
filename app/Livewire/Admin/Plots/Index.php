<?php

namespace App\Livewire\Admin\Plots;

use App\Models\Plot;
use App\Models\User;
use App\Livewire\Concerns\WithToastNotifications;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $search        = '';
    public $activeFilter  = '';
    public $roleFilter    = 'all';
    // Reassign modal
    public $showReassignModal    = false;
    public $reassignPlotId       = null;
    public $reassignPlotName     = '';
    public $reassignViticulturistId = '';
    public $reassignSearch       = '';

    protected $queryString = [
        'search', 'activeFilter', 'roleFilter',
    ];

    public function updatingSearch()       { $this->resetPage(); }
    public function updatingActiveFilter() { $this->resetPage(); }
    public function updatingRoleFilter()   { $this->resetPage(); }

    // ─── Reassign ─────────────────────────────────────────────────────────────

    public function openReassignModal($plotId)
    {
        $plot = Plot::findOrFail($plotId);
        $this->reassignPlotId       = $plotId;
        $this->reassignPlotName     = $plot->name;
        $this->reassignViticulturistId = $plot->viticulturist_id ?? '';
        $this->reassignSearch       = '';
        $this->showReassignModal    = true;
        $this->resetValidation();
    }

    public function closeReassignModal()
    {
        $this->showReassignModal = false;
        $this->reassignPlotId    = null;
    }

    public function reassignViticulturist()
    {
        $this->validate([
            'reassignViticulturistId' => 'required|exists:users,id',
        ], [
            'reassignViticulturistId.required' => __('Selecciona un viticultor.'),
            'reassignViticulturistId.exists'   => __('El usuario seleccionado no existe.'),
        ]);

        $plot = Plot::findOrFail($this->reassignPlotId);
        $previousOwnerId = $plot->viticulturist_id;
        $plot->viticulturist_id = $this->reassignViticulturistId;
        $plot->save();

        $newOwner = User::find($this->reassignViticulturistId);

        SecurityLogger::logSecurityEvent('plot_reassigned', [
            'admin_id'          => Auth::id(),
            'plot_id'           => $plot->id,
            'plot_name'         => $plot->name,
            'from_user_id'      => $previousOwnerId,
            'to_user_id'        => (int) $this->reassignViticulturistId,
            'to_user_name'      => $newOwner->name,
        ]);

        $this->closeReassignModal();
        $this->toastSuccess("Parcela \"{$plot->name}\" reasignada a {$newOwner->name}.");
    }

    // ─── Export ───────────────────────────────────────────────────────────────

    public function exportCsv()
    {
        $plots    = Plot::with(['viticulturist', 'municipality.province'])->orderBy('created_at', 'desc')->get();
        $filename = 'parcelas_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($plots) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', 'Nombre', 'Viticultor', 'Email Viticultor', 'Rol', 'Municipio', 'Provincia', 'Área (ha)', 'Estado', 'Registro']);

            foreach ($plots as $plot) {
                fputcsv($handle, [
                    $plot->id,
                    $plot->name,
                    $plot->viticulturist?->name ?? '',
                    $plot->viticulturist?->email ?? '',
                    $plot->viticulturist?->role ?? '',
                    $plot->municipality?->name ?? '',
                    $plot->municipality?->province?->name ?? '',
                    number_format($plot->area, 2),
                    $plot->active ? 'Activa' : 'Inactiva',
                    $plot->created_at->format('d/m/Y'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $query = Plot::query()
            ->select(['id', 'name', 'description', 'area', 'active', 'viticulturist_id', 'municipality_id', 'created_at', 'updated_at'])
            ->with([
                'viticulturist:id,name,email,role',
                'municipality:id,name,province_id',
                'municipality.province:id,name',
                'sigpacCodes:id,code',
            ]);

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$search])
                  ->orWhereHas('viticulturist', function ($q) use ($search) {
                      $q->whereRaw('LOWER(name) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
                  });
            });
        }

        if ($this->activeFilter !== '') {
            $query->where('active', $this->activeFilter === '1');
        }

        if ($this->roleFilter !== 'all') {
            $query->whereHas('viticulturist', fn($q) => $q->where('role', $this->roleFilter));
        }

        $plots = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate(20);

        // Viticulturists available for reassign search
        $viticulturistQuery = User::whereIn('role', ['viticulturist', 'winery', 'producer', 'supervisor']);
        if ($this->reassignSearch) {
            $s = '%' . strtolower($this->reassignSearch) . '%';
            $viticulturistQuery->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(name) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$s]);
            });
        }
        $availableUsers = $this->showReassignModal
            ? $viticulturistQuery->orderBy('name')->limit(15)->get(['id', 'name', 'email', 'role'])
            : collect();

        $realBase = Plot::query();
        $stats = [
            'total'      => $realBase->count(),
            'active'     => (clone $realBase)->where('active', true)->count(),
            'total_area' => (clone $realBase)->sum('area') ?? 0,
            'by_role'    => [
                'viticulturist' => (clone $realBase)->whereHas('viticulturist', fn($q) => $q->where('role', 'viticulturist'))->count(),
                'winery'        => (clone $realBase)->whereHas('viticulturist', fn($q) => $q->where('role', 'winery'))->count(),
                'supervisor'    => (clone $realBase)->whereHas('viticulturist', fn($q) => $q->where('role', 'supervisor'))->count(),
            ],
        ];

        return view('livewire.admin.plots.index', [
            'plots'          => $plots,
            'stats'          => $stats,
            'availableUsers' => $availableUsers,
        ])->layout('layouts.app', [
            'title'       => __('Parcelas - Admin - Agro365'),
            'description' => __('Visualiza y gestiona todas las parcelas del sistema'),
        ]);
    }
}
