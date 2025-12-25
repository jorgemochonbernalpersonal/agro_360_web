<?php

namespace App\Livewire\Viticulturist\Plots;

use App\Models\Plot;
use App\Models\PlotAuditLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class PlotAuditHistory extends Component
{
    use WithPagination;

    public Plot $plot;
    public $filterUser = '';
    public $filterAction = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    protected $queryString = [
        'filterUser' => ['except' => ''],
        'filterAction' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
    ];

    public function mount(Plot $plot = null)
    {
        if (!$plot) {
            abort(404, 'Parcela no encontrada.');
        }
        
        // Verificar autorización
        if (!Auth::user()->can('view', $plot)) {
            abort(403, 'No tienes permiso para ver el historial de esta parcela.');
        }

        $this->plot = $plot;
    }

    public function render()
    {
        $query = PlotAuditLog::where('plot_id', $this->plot->id)
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($this->filterUser) {
            $query->where('user_id', $this->filterUser);
        }

        if ($this->filterAction) {
            $query->where('action', $this->filterAction);
        }

        if ($this->filterDateFrom) {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->whereDate('created_at', '<=', $this->filterDateTo);
        }

        $logs = $query->paginate(10);

        // Obtener usuarios únicos que han modificado esta parcela
        $users = User::whereIn('id', 
            PlotAuditLog::where('plot_id', $this->plot->id)
                ->distinct()
                ->pluck('user_id')
        )->get();

        // Acciones disponibles
        $actions = PlotAuditLog::where('plot_id', $this->plot->id)
            ->distinct()
            ->pluck('action');

        return view('livewire.viticulturist.plots.plot-audit-history', [
            'logs' => $logs,
            'users' => $users,
            'actions' => $actions,
        ]);
    }

    public function clearFilters()
    {
        $this->filterUser = '';
        $this->filterAction = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->resetPage();
    }

    public function getChangeDiff($log)
    {
        if (empty($log->changes)) {
            return [];
        }

        $changes = $log->changes;
        $diff = [];

        if (isset($changes['old']) && isset($changes['new'])) {
            foreach ($changes['new'] as $field => $newValue) {
                $oldValue = $changes['old'][$field] ?? null;
                
                if ($oldValue != $newValue) {
                    $diff[] = [
                        'field' => $this->getFieldLabel($field),
                        'old' => $this->formatValue($oldValue, $field),
                        'new' => $this->formatValue($newValue, $field),
                    ];
                }
            }
        }

        return $diff;
    }

    protected function getFieldLabel($field)
    {
        $labels = [
            'name' => 'Nombre',
            'surface_area' => 'Superficie (ha)',
            'location' => 'Ubicación',
            'cadastral_reference' => 'Referencia catastral',
            'province_id' => 'Provincia',
            'municipality_id' => 'Municipio',
            'autonomous_community_id' => 'Comunidad Autónoma',
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    protected function formatValue($value, $field = null)
    {
        if ($value === null) {
            return '<span class="text-gray-400 italic">vacío</span>';
        }

        if (is_bool($value)) {
            return $value ? '✅ Sí' : '❌ No';
        }

        // Formatear IDs de relaciones
        if ($field === 'province_id' && $value) {
            $province = \App\Models\Province::find($value);
            return $province ? $province->name : $value;
        }

        if ($field === 'municipality_id' && $value) {
            $municipality = \App\Models\Municipality::find($value);
            return $municipality ? $municipality->name : $value;
        }

        if ($field === 'autonomous_community_id' && $value) {
            $community = \App\Models\AutonomousCommunity::find($value);
            return $community ? $community->name : $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        return $value;
    }
}
